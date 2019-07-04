var isInIframe = function () {
  try {
    return window.self !== window.top;
  } catch (e) {
    return true;
  }
}
if (isInIframe()) {
  var target = parent;
  var origin =
    document.location.origin ||
    document.location.protocol + "//" + document.location.host;
  var message = {
    userLoggedIn: true
  };
  target.postMessage(message, origin);
} else {
  document.location = "/";
}