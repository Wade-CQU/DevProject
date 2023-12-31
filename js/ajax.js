function postAJAX(url, data, jsonBack = true) {
  return new Promise(function(resolve, reject) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        if (jsonBack) {
          try {
            // console.log(xhr.responseText);
            var responseData = JSON.parse(xhr.responseText);
          } catch (e) {
            reject(new Error('No data returned. Request failed with status: ' + xhr.status));
          } finally {
            resolve(responseData);
          }
        } else {
          resolve(xhr.responseText);
        }
      } else {
        reject(new Error('Request failed with status: ' + xhr.status));
      }
    };
    xhr.onerror = function() {
      reject(new Error('Network error'));
    };
    xhr.send(data);
  });
}
