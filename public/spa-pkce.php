<!doctype html>
<html lang="en">
<body style="font-family: sans-serif; font-size: 150%">
<div id="message"></div>
<script defer>
  // credit https://developer.okta.com/blog/2019/05/01/is-the-oauth-implicit-flow-dead
  function randomString() {
    let arr = new Uint32Array(28);
    window.crypto.getRandomValues(arr);
    return Array.from(arr, dec => ('0' + dec.toString(16)).substr(-2)).join('');
  }

  async function sha256(plain) {
    const encoder = new TextEncoder();
    const data = encoder.encode(plain);
    return window.crypto.subtle.digest('SHA-256', data);
  }

  // Base64-urlencodes the input string
  function base64urlencode(str) {
    // Convert the ArrayBuffer to string using Uint8 array to convert to what btoa accepts.
    // btoa accepts chars only within ascii 0-255 and base64 encodes them.
    // Then convert the base64 encoded to base64url encoded
    //   (replace + with -, replace / with _, trim trailing =)
    return btoa(String.fromCharCode.apply(null, new Uint8Array(str)))
      .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  }

  async function onReady() {
    let message = document.getElementById('message');
    function addToMessage(text) {
      message.innerHTML += '<div>' + text + '</div>';
    }

    if (!window.location.search) { // we haven't authenticated yet; redirect
      let state = randomString();
      localStorage.setItem('oauthState', state);

      let codeChallengeSecret = randomString();
      localStorage.setItem('oauthChallengeSecret', codeChallengeSecret);

      message.innerText = 'Redirecting to authorization page after 3 seconds...';
      setTimeout(async () => {
        window.location = '/oauth/authorize?' +
          'client_id=single-page-app&' +
          'response_type=code&' +
          'scope=me.name%20me.hash&' +
          'state=' + state + '&' +
          'code_challenge_method=S256&' +
          'redirect_uri=http://localhost/spa-pkce.php&' +
          'code_challenge=' + base64urlencode(await sha256(codeChallengeSecret));
      }, 3000);

      return;
    }

    // we've been redirected back from auth
    let qsValues = {};

    let qsMessage = '<h3>Query String Parameters</h3><dl>';

    for (const value of window.location.search.substring(1).split('&')) {
      const [k, v] = value.split('=');
      qsValues[k] = v;
      qsMessage += '<dt>' + k + '</dt><dd>' + v + '</dd>';
    }

    addToMessage(qsMessage + '</dl>');

    if (qsValues.code === undefined) {
      return; // we got an error response back; bail
    }

    addToMessage(localStorage.getItem('oauthState') === qsValues.state ? 'State matches!' : 'MISMATCHED STATE');

    function urlencode(data) {
      return Object.keys(data)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(data[key]))
        .join('&');
    }

    // redeem the auth code for an access token
    const accessTokenResponse = await (await fetch('/oauth/token', {
      method: 'POST',
      headers: {'Content-type': 'application/x-www-form-urlencoded'},
      body: urlencode({
        client_id: 'single-page-app',
        grant_type: 'authorization_code',
        code: qsValues.code,
        code_verifier: localStorage.getItem('oauthChallengeSecret'),
        redirect_uri: 'http://localhost/spa-pkce.php'
      })
    })).json();

    // show access token info
    let accessTokenMessage = '<h3>Access Token Response</h3><dl>';

    for (const k in accessTokenResponse) {
      accessTokenMessage += '<dt>' + k + '</dt><dd>' + accessTokenResponse[k] + '</dd>';
    }

    addToMessage(accessTokenMessage + '</dl>');

    // use the access token to grab user info

    const userInfo = await (await fetch('/api/me', {
      headers: {Authorization: 'Bearer ' + accessTokenResponse.access_token}
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
