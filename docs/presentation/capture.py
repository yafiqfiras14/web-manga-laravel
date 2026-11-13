"""Capture screenshots for the SoundKu presentation deck.

Uses headless Chrome through the DevTools protocol so we can log into the
admin panel and start playback before taking the picture.
"""

import base64
import json
import os
import shutil
import subprocess
import tempfile
import time
import urllib.request

import websocket

CHROME = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
PORT = 9333
BASE = "http://127.0.0.1:8000"
ASSETS = os.path.join(os.path.dirname(os.path.abspath(__file__)), "assets")


def http_json(url):
    with urllib.request.urlopen(url, timeout=3) as response:
        return json.loads(response.read().decode("utf-8"))


def wait_for_target(timeout=25):
    deadline = time.time() + timeout
    while time.time() < deadline:
        try:
            for target in http_json(f"http://127.0.0.1:{PORT}/json"):
                if target.get("type") == "page" and target.get("webSocketDebuggerUrl"):
                    return target["webSocketDebuggerUrl"]
        except Exception:
            pass
        time.sleep(0.4)
    raise RuntimeError("Chrome DevTools target not found")


class CDP:
    def __init__(self, url):
        self.ws = websocket.create_connection(url, timeout=30)
        self.next_id = 1

    def call(self, method, **params):
        message_id = self.next_id
        self.next_id += 1
        self.ws.send(json.dumps({"id": message_id, "method": method, "params": params}))
        while True:
            data = json.loads(self.ws.recv())
            if data.get("id") == message_id:
                if "error" in data:
                    raise RuntimeError(f"{method}: {data['error']}")
                return data.get("result", {})

    def evaluate(self, expression, await_promise=False):
        result = self.call(
            "Runtime.evaluate",
            expression=expression,
            returnByValue=True,
            awaitPromise=await_promise,
        )
        return result.get("result", {}).get("value")

    def goto(self, url, settle=3.0):
        self.call("Page.navigate", url=url)
        time.sleep(settle)

    def shot(self, name):
        data = self.call("Page.captureScreenshot", format="png", fromSurface=True)
        path = os.path.join(ASSETS, name)
        with open(path, "wb") as handle:
            handle.write(base64.b64decode(data["data"]))
        print("saved", path, os.path.getsize(path), "bytes")


def main():
    os.makedirs(ASSETS, exist_ok=True)
    profile = tempfile.mkdtemp(prefix="soundku-cdp-")
    process = subprocess.Popen(
        [
            CHROME,
            "--headless=new",
            "--disable-gpu",
            "--hide-scrollbars",
            "--no-first-run",
            "--autoplay-policy=no-user-gesture-required",
            "--remote-debugging-port=" + str(PORT),
            "--window-size=1440,900",
            "--user-data-dir=" + profile,
            "about:blank",
        ],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )

    try:
        cdp = CDP(wait_for_target())
        cdp.call("Page.enable")
        cdp.call("Runtime.enable")

        # --- player in action -------------------------------------------------
        cdp.goto(BASE + "/track/2250427187", settle=5)
        started = cdp.evaluate(
            """
            (() => {
                const img = document.querySelector('main img');
                const title = (document.querySelector('h1') || {}).textContent || 'What\\'s Luv?';
                const artist = 'iskey';
                const track = {
                    id: '2250427187',
                    title: title.trim(),
                    artist: artist,
                    artwork: img ? img.src : null,
                    is_preview: false,
                };
                if (!window.SoundKu || !SoundKu.player) return 'no-player';
                SoundKu.player.playQueue([track], 0);
                return 'started';
            })()
            """
        )
        print("playback:", started)
        time.sleep(8)
        state = cdp.evaluate(
            """
            (() => {
                const a = document.getElementById('audio');
                return JSON.stringify({
                    paused: a.paused,
                    currentTime: Math.round(a.currentTime),
                    duration: Math.round(a.duration),
                    label: (document.getElementById('time-duration') || {}).textContent,
                });
            })()
            """
        )
        print("audio:", state)
        cdp.shot("player.png")

        # --- admin panel ------------------------------------------------------
        cdp.goto(BASE + "/admin/login", settle=3)
        submitted = cdp.evaluate(
            """
            (() => {
                const form = document.querySelector('form');
                if (!form) return 'no-form';
                form.querySelector('#email').value = 'admin@soundku.test';
                form.querySelector('#password').value = 'password';
                form.submit();
                return 'submitted';
            })()
            """
        )
        print("login:", submitted)
        time.sleep(4)
        cdp.goto(BASE + "/admin/featured", settle=4)
        rows = cdp.evaluate("document.querySelectorAll('tbody tr').length")
        print("admin rows:", rows)
        cdp.shot("admin.png")
    finally:
        process.terminate()
        try:
            process.wait(timeout=10)
        except Exception:
            process.kill()
        shutil.rmtree(profile, ignore_errors=True)


if __name__ == "__main__":
    main()
