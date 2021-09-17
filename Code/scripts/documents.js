(function() {
    sendForm();
})();

const selectResult = document.getElementById("sort");
selectResult.addEventListener("change", sendForm);
const selectType = document.getElementById("sortType");
selectType.addEventListener("change", sendForm);

function sendForm() {
    const sortOption = document.getElementById("sort").value;
    const orderType = document.getElementById("sortType").value;

    var data = {
        sortOption: sortOption,
        orderType: orderType
    };

    sendRequest('getDocuments.php', { method: 'POST', data: `data=${JSON.stringify(data)}` }, load, console.error);
}

function reloadTable(response) {
    if (response.success) {
        sendForm();
    }
}

function load(response) {
    const tableBody = document.getElementById('tbody');
    tableBody.innerHTML=" ";

    if (response.success) {
        const finishedStatus = "Приключен"; 
        const archivedStatus = "Архивиран";

        var data = response.data;
        if (data.length == 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = "6";
            td.innerHTML = "В момента няма качени документи.";
            td.style.textAlign = "center";
            td.style.padding = "10px";
            tr.appendChild(td);
            tableBody.appendChild(tr);
        }

        data.forEach(element => {
            var tr = document.createElement('tr');

            var name = element.name;
            var id = element.id;
            var uploaded = new Date(element.uploaded * 1000);
            var status = element.status;
            var color = element.color;
            
            var idTd = document.createElement('td');
            idTd.innerHTML = id;
            tr.appendChild(idTd);

            var uploadedTd = document.createElement('td');
            uploadedTd.innerHTML = uploaded.toLocaleString('ro-RO');
            tr.appendChild(uploadedTd);

            var departmentTd = document.createElement('td');
            departmentTd.innerHTML = element.department;
            tr.appendChild(departmentTd);

            var nameTd = document.createElement('td');
            nameTd.innerHTML = name;
            tr.appendChild(nameTd);

            var statusTd = document.createElement('td');
            statusTd.classList.add("status");
            statusTd.innerHTML = status;
            statusTd.style.background = "#" + color;
            tr.appendChild(statusTd);

            var operationsTd = document.createElement('td');
            var buttonsSection = document.createElement('section');
            buttonsSection.classList.add("buttons-section");

            if (status !== archivedStatus) {
                var viewBtn = document.createElement('a');
                viewBtn.classList.add("button", "view");
                viewBtn.innerHTML = 'Преглед';
                viewBtn.onclick = function () {
                    location.href = "viewDocument.php?id=" + id;
                };
                buttonsSection.appendChild(viewBtn);

                var downloadBtn = document.createElement('a');
                downloadBtn.classList.add("button", "download");
                downloadBtn.innerHTML = 'Изтегли';
                let downloadPath = element.path;
                let downloadFilename = "File-" + element.id + "-" 
                    + uploaded.toLocaleDateString("ro-RO", { day: 'numeric' }) + "-"
                    + uploaded.toLocaleDateString("ro-RO", { month: 'numeric' }) + "-" 
                    + uploaded.toLocaleDateString("ro-RO", { year: 'numeric' });

                if (downloadPath.match(/index\.html$/)) {
                    downloadPath = downloadPath.replace(/index\.html$/, "archive.zip");
                    downloadFilename += ".zip";
                }
                else {
                    downloadFilename += ".pdf";
                }

                downloadBtn.setAttribute("href", downloadPath);
                downloadBtn.setAttribute("download", downloadFilename);

                downloadBtn.onclick = function () {
                    let data = {
                        operation: 'download',
                        id: element.id
                    };
                    sendRequest('modifyDocument.php', { method: 'POST', data: `data=${JSON.stringify(data)}` }, () => {}, console.error);
                };

                buttonsSection.appendChild(downloadBtn);
            } else {
                var unarchiveBtn = document.createElement('a');
                unarchiveBtn.classList.add("button", "archive");
                unarchiveBtn.innerHTML = 'Разархивирай';

                unarchiveBtn.onclick = function () {
                    let data = {
                        operation: 'unarchive',
                        id: element.id
                    };
                    sendRequest('modifyDocument.php', { method: 'POST', data: `data=${JSON.stringify(data)}` }, reloadTable, console.error);
                };

                buttonsSection.appendChild(unarchiveBtn);
            }

            if (response.admin !== undefined && response.admin === true && status == finishedStatus) {
                var archiveBtn = document.createElement('a');
                archiveBtn.classList.add("button", "archive");
                archiveBtn.innerHTML = 'Архивирай';

                archiveBtn.onclick = function () {
                    let data = {
                        operation: 'archive',
                        id: element.id
                    };
                    sendRequest('modifyDocument.php', { method: 'POST', data: `data=${JSON.stringify(data)}` }, reloadTable, console.error);
                };

                buttonsSection.appendChild(archiveBtn);
            }

            operationsTd.appendChild(buttonsSection);
            tr.appendChild(operationsTd);            
            tableBody.appendChild(tr);
        });
    } else {
        var errors = document.getElementById('errors');
        errors.innerHTML = response.data;
    }
}