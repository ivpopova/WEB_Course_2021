// File upload JS
const inputElement = document.querySelector("input.drop-zone__input");
const dropZoneElement = document.querySelector("section.drop-zone");

if (inputElement && dropZoneElement) {
    dropZoneElement.addEventListener("click", (e) => {
        inputElement.click();
    });

    inputElement.addEventListener("change", (e) => {
        if (inputElement.files.length) {
            updateThumbnail(dropZoneElement, inputElement.files[0]);
        }
    });

    dropZoneElement.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZoneElement.classList.add("drop-zone--over");
    });

    ["dragleave", "dragend"].forEach((type) => {
        dropZoneElement.addEventListener(type, (e) => {
            dropZoneElement.classList.remove("drop-zone--over");
        });
    });

    dropZoneElement.addEventListener("drop", (e) => {
        e.preventDefault();
        if (e.dataTransfer.files.length) {
            inputElement.files = e.dataTransfer.files;
            updateThumbnail(dropZoneElement, e.dataTransfer.files[0]);
        }
        dropZoneElement.classList.remove("drop-zone--over");
    });
}

const uploadForm = document.getElementById("upload-form");
if (uploadForm){
    uploadForm.addEventListener("submit", (event) => {
        event.preventDefault();        
    });

    const uploadButton = document.getElementById("upload-btn");
    uploadButton.addEventListener("click", function(){
        if (validateUpload()) {
            uploadForm.submit();
        }
    });
}

// Login form

const loginForm = document.getElementById("login-form");
if (loginForm){
    loginForm.addEventListener("submit", (event) => {
        event.preventDefault();        
    });

    const loginButton = document.getElementById("login-btn");
    loginButton.addEventListener("click", function(){

        if (validateLogin()) {
            loginForm.submit();
        }

    });
}

// Track form

const trackForm = document.getElementById("track-form");
if (trackForm) {
    trackForm.addEventListener("submit", (event) => {
        event.preventDefault();        
    });

    const trackButton = document.getElementById("track-btn");
    trackButton.addEventListener("click", function(){
        if (validateTracking()) {
            trackForm.submit();
        }
    });
}

// View document change status button

const changeStatusSelect = document.querySelector("section.changeStatus select");
if (changeStatusSelect) {
    changeStatusSelect.addEventListener("change", function(){
        let selectedIndex = changeStatusSelect.selectedIndex;

        const options = document.querySelectorAll("section.changeStatus option");
        let selectedOptionColor = options[selectedIndex].style.backgroundColor;
        
        this.style.backgroundColor = selectedOptionColor;
    });
}

const changeStatusButton = document.getElementById("change-btn");
if (changeStatusButton) {
    changeStatusButton.addEventListener("click", function(){
        let getParams = new URLSearchParams(window.location.search);
        let statusId = changeStatusSelect.value;
        let data = {
            operation: 'change',
            id: getParams.get('id'),
            status: statusId
        };
        sendRequest('modifyDocument.php', { method: 'POST', data: `data=${JSON.stringify(data)}` }, updateStatus, console.error);
    });
}

// Back button in view document

const backButton = document.querySelector("header.sticky a.button.back");
if (backButton) {
    backButton.addEventListener("click", function(){
        window.location.href = "documents.php";
    });
}

// Download button in view document

const downloadButton = document.querySelector("header.sticky a.button.download");
if (downloadButton) {
    downloadButton.addEventListener("click", function(){
        let getParams = new URLSearchParams(window.location.search);
        let data = {
            operation: 'download',
            id: getParams.get('id')
        };
        sendRequest('modifyDocument.php', { method: 'POST', data: `data=${JSON.stringify(data)}` }, () => {}, console.error);
    });
}

// Misc. functions

function updateStatus(response) {
    if (response.success) {
        const statusElement = document.querySelector("header.sticky p.status mark");
        let selectedIndex = changeStatusSelect.selectedIndex;
        const options = document.querySelectorAll("section.changeStatus option");
        statusElement.innerHTML = options[selectedIndex].innerHTML;
        statusElement.style.backgroundColor = changeStatusSelect.style.backgroundColor;
    }
}

function updateThumbnail(dropZoneElement, file) {
    if (file.type != "application/pdf" && (!file.type.match(/application\/.*zip.*/))) {
        alert ("Разрешени файлови формати са: pdf, zip.");
    }
    else {
        let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");

        // First time - remove the prompt
        if (dropZoneElement.querySelector(".drop-zone__prompt")) {
            dropZoneElement.querySelector(".drop-zone__prompt").remove();
        }

        // First time - there is no thumbnail element, so lets create it
        if (!thumbnailElement) {
            thumbnailElement = document.createElement("section");
            thumbnailElement.classList.add("drop-zone__thumb");
            dropZoneElement.appendChild(thumbnailElement);
        }

        thumbnailElement.dataset.label = file.name;

        if (file.type == "application/pdf") {
            thumbnailElement.style.backgroundImage = "url('template/icons/pdf.png')";
        } 
        else if (file.type.match(/application\/.*zip.*/)) {
            thumbnailElement.style.backgroundImage = "url('template/icons/zip.png')";
        } else {
            thumbnailElement.style.backgroundImage = null;
        }   
    }
}

function validateUpload() {
    const fileInput = document.getElementById("document");
    const nameInput = document.getElementById("name");
    const departmentInput = document.getElementById("department");
    const descriptionInput = document.getElementById("description");

    if (fileInput.files.length == 0) {
        alert("Моля, изберете pdf или zip файл.");
        return false;
    }

    const file = fileInput.files[0];
    if (file.type != "application/pdf" && (!file.type.match(/application\/.*zip.*/))) {
        alert("Разрешени файлови формати са: pdf, zip.");
        return false;
    }

    if (nameInput.value.length == 0) {
        alert("Моля, изберете име.");
        return false;
    }

    if (departmentInput.value.length == 0) {
        alert("Моля, изберете отдел!");
        return false;
    }

    if (descriptionInput.value.length == 0) {
        alert("Моля, въведете описание на документа.");
        return false;
    }

    return true;
}

function validateLogin() {
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");

    if (usernameInput.value.length == 0) {
        alert("Моля, въведете потребителско име!");
        return false;
    }

    if (passwordInput.value.length == 0) {
        alert("Моля, въведете парола.");
        return false;
    }

    return true;
}

function validateTracking() {
    const codeInput = document.getElementById("code");

    if (codeInput.value.length != 15) {
        alert("Моля, въведете валиден 15-цифрен код.");
        return false;
    }

    return true;
}

function sendRequest(url, options, successCallback, errorCallback) { 
    var request = new XMLHttpRequest();

    request.addEventListener('load', function() { 
        var response = JSON.parse(request.responseText);

        if (request.status === 200) {
            successCallback(response);
        } else {
            errorCallback(response);
        }
    });

    request.open(options.method, url, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.send(options.data);
}