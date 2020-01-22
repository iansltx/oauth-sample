<!doctype html>
<html lang="en">
    <body style="font-family: sans-serif; font-size: 150%">
    <div id="message"></div>
    <script defer>
        function randomString() {
            let arr = new Uint32Array(28);
            window.crypto.getRandomValues(arr);
            return Array.from(arr, dec => ('0' + dec.toString(16)).substr(-2)).join('');
        }

        async function onReady() {
            let message = document.getElementById('message');
            function addToMessage(text) {
                message.innerHTML += '<div>' + text + '</div>';
            }

            if (window.location.search) { // we probably got some errors back; handle 'em
                let errorMessage = '<h3>Query String Parameters</h3><dl>';
                let state;

                for (const value of window.location.search.substring(1).split('&')) {
                    const [k, v] = value.split('=');
                    if (k === 'state') {
                        state = v;
                    }
                    errorMessage += '<dt>' + k + '</dt><dd>' + v.replace('+', ' ') + '</dd>';
                }

                addToMessage(errorMessage + '</dl>');

                addToMessage(localStorage.getItem('oauthState') === state ? 'State matches!' : 'MISMATCHED STATE');

                return;
            }

            if (!window.location.hash) { // we haven't authenticated yet; redirect
                let state = randomString();
                localStorage.setItem('oauthState', state);

                message.innerText = 'Redirecting to authorization page after 3 seconds...';
                setTimeout(() => {
                    window.location = '/oauth/authorize?' +
                        'client_id=single-page-app&' +
                        'response_type=token&' +
                        'scope=me.name%20me.hash&' +
                        'redirect_uri=http://localhost/spa-implicit.php&' +
                        'state=' + state;
                }, 3000);

                return;
            }

            // we've been redirected back from auth; grab the access token and use it to make a call
            let hashValues = {};

            let accessTokenMessage = '<h3>URL Fragment Parameters</h3><dl>';

            for (const value of window.location.hash.substring(1).split('&')) {
                const [k, v] = value.split('=');
                hashValues[k] = v;
                accessTokenMessage += '<dt>' + k + '</dt><dd>' + v + '</dd>';
            }

            addToMessage(accessTokenMessage + '</dl>');

            if (hashValues.access_token === undefined) {
                return; // we got an error response back; bail
            }

            addToMessage(localStorage.getItem('oauthState') === hashValues.state ? 'State matches!' : 'MISMATCHED STATE');

            // use the access token to grab user info

            const userInfo = await (await fetch('/api/me', {
                headers: {Authorization: 'Bearer ' + hashValues.access_token}
            })).json();

            let userInfoMessage = '<h3>User Info</h3><dl>';

            for (const k in userInfo) {
                userInfoMessage += '<dt>' + k + '</dt><dd>' + userInfo[k] + '</dd>';
            }

            addToMessage(userInfoMessage + '</dl>');
        }

        onReady();
    </script>
    </body>
</html>
