function copyToClipboard(id) {
    // Get the current URL and remove the last '/' if it exists
    let url = window.location.href.split('#')[0]; // Split the URL by '#' to get the base URL
    if (url.endsWith('/')) {
        url = url.slice(0, -1); // Remove the last '/' if present
    }

    // Append the heading's id to the URL
    url = url + '#' + id;

    // Create a temporary text area to copy the URL
    const textArea = document.createElement('textarea');
    textArea.value = url;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');  // Execute the copy command
    document.body.removeChild(textArea);

    // Set the message based on the language
    let language = navigator.language || navigator.userLanguage; // Get the browser language
    let message = '';

    if (language.startsWith('ko')) {
        message = 'URL이 복사되었습니다'; // Korean message
    } else {
        message = 'URL has been copied'; // English message
    }

    // Alert the user that the URL has been copied
    alert(message);
}
