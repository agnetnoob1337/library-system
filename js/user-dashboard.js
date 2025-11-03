document.addEventListener('DOMContentLoaded', function() {
    
    
    document.getElementById("available-media-table-body").addEventListener("change", function(e) {
        var checkboxes = document.querySelectorAll(".available-media-checkbox");

        if (e.target && e.target.classList.contains("available-media-checkbox")) {
            document.querySelectorAll(`.available-media-checkbox`).forEach(otherCheckbox => {
                if (otherCheckbox !== e.target) {
                    otherCheckbox.checked = false;
                }
            });
        }
    });


    loadAllMedia();

    document.getElementById("checkout").addEventListener("click", function() {
        var selectedCheckbox = document.querySelector(".available-media-checkbox:checked");
        if (selectedCheckbox) {
            var mediaId = selectedCheckbox.value;
            
            fetch(`./php/media-checkout.php?mediaId=${mediaId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mediaId: mediaId })

            }).then(response => {
                return response.json();
            }).then(data => {
                if (data.success) {
                    alert("Media checked out successfully!");
                    loadAllMedia();
                } else {
                    alert("Error checking out media: " + data.message);
                }
            }).catch(error => {
                console.error('Error during checkout:', error);
            });
        } else {
            alert("Snälla välj ett media att låna och klicka igen.");
        }
    });

    document.getElementById("return").addEventListener("click", function() {
        var selectedCheckbox = document.querySelector(".borrowed-media-checkbox:checked");
        if (selectedCheckbox) {
            var mediaId = selectedCheckbox.value;
            
            fetch(`./php/media-return.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mediaIds: mediaId })

            }).then(response => {
                return response.json();
            }).then(data => {
                loadAllMedia();
                if (data.success) {
                    alert("Media returned successfully!");
                    location.reload();
                } else {
                    alert("Error returning media: " + data.message);
                }
            }).catch(error => {
                console.error('Error during return:', error);
            });
        } else {
            alert("Snälla välj ett media att återlämna och klicka igen.");
        }
    });

    document.getElementById("media-type").addEventListener("change", function() {
        var selectedType = this.value;
        loadAllMedia(selectedType);
    });

    document.getElementById("search-input").addEventListener("input", function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll("#available-media-table-body tr");
        rows.forEach(row => {
            var title = row.cells[1].textContent.toLowerCase();
            var author = row.cells[2].textContent.toLowerCase();
            if (title.includes(filter) || author.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});

async function loadAllMedia(mediaType = '', ) {
    // Function to load all media items of a specific type
    var SABCategories = [];
    await fetch('./php/get-sab-categories.php').then(response => {
        return response.json();
    }).then(data => {
        SABCategories = data;

    }).catch(error => {
        console.error('Error fetching SAB categories:', error);
    });

    var availableMediaTableBody = document.getElementById("available-media-table-body");
    var borrowedMediaTableBody = document.getElementById("borrowed-media-table-body");
    var lateReturnsTableBody = document.getElementById("late-returns-media-table-body");

    while (availableMediaTableBody.rows.length > 0) {
        availableMediaTableBody.deleteRow(0);
    }
    while (borrowedMediaTableBody.rows.length > 0) {
        borrowedMediaTableBody.deleteRow(0);
    }
    while (lateReturnsTableBody.rows.length > 0) {
        lateReturnsTableBody.deleteRow(0);
    }


    if(mediaType === '') {
        var apiCall = "./php/get-media.php?availableOnly=true";
    }
    else{
        var apiCall = "./php/get-media.php?availableOnly=true&filter=" + mediaType;
    }
    fetch(apiCall).then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 


        data.forEach(media => {
            var row = document.createElement("tr");

            var selectionCell = document.createElement("td");
            var checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.classList.add("available-media-checkbox");
            checkbox.value = media.id;
            selectionCell.appendChild(checkbox);
            row.appendChild(selectionCell);


            var titleCell = document.createElement("td");
            titleCell.textContent = media.title;
            row.appendChild(titleCell);

            var authorCell = document.createElement("td");
            authorCell.textContent = media.author;
            row.appendChild(authorCell);

            var isbnCell = document.createElement("td");
            isbnCell.textContent = media.ISBN;
            row.appendChild(isbnCell);

            var IMDBCell = document.createElement("td");
            IMDBCell.textContent = media.IMDB;
            row.appendChild(IMDBCell);

            var categoryCell = document.createElement("td");
            var SABRow = SABCategories.find(cat => cat.signum === media.SAB_signum);
            categoryCell.textContent = SABRow.category;
            row.appendChild(categoryCell);

            var typeCell = document.createElement("td");
            if(media.book) {
                typeCell.textContent = "Bok";
            } else if(media.audiobook) {
                typeCell.textContent = "Ljudbok";
            } else if(media.film) {
                typeCell.textContent = "Film";
            }
            row.appendChild(typeCell);

            availableMediaTableBody.appendChild(row);
        });
    }).catch(error => {
        console.error('Error fetching media data:', error);
    });

    fetch("./php/get-user-loans.php").then(response => {
        return response.json();
    }).then(data => {

        console.log(data);
        data.forEach(loan => {
            var row = document.createElement("tr");

            var selectCell = document.createElement("td");
            var checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.classList.add("borrowed-media-checkbox");
            checkbox.value = loan.id;
            selectCell.appendChild(checkbox);
            row.appendChild(selectCell);

            var titleCell = document.createElement("td");
            titleCell.textContent = loan.title;
            row.appendChild(titleCell);

            var authorCell = document.createElement("td");
            authorCell.textContent = loan.author;
            row.appendChild(authorCell);

            var isbnCell = document.createElement("td");
            isbnCell.textContent = loan.ISBN;
            row.appendChild(isbnCell);
        

            var categoryCell = document.createElement("td");
            var SABRow = SABCategories.find(cat => cat.signum === loan.SAB_signum);
            categoryCell.textContent = SABRow.category;
            row.appendChild(categoryCell);

            var mediaTypeCell = document.createElement("td");
            if(loan.book) {
                mediaTypeCell.textContent = "Bok";
            } else if(loan.audioBook) {
                mediaTypeCell.textContent = "Ljudbok";
            } else if(loan.film) {
                mediaTypeCell.textContent = "Film";
            }
            row.appendChild(mediaTypeCell);

            var dueDateCell = document.createElement("td");
            dueDateCell.textContent = loan.return_date;
            row.appendChild(dueDateCell);

            borrowedMediaTableBody.appendChild(row);
        });
    }).catch(error => {
        console.error('Error fetching user loans:', error);
    });

    fetch("./php/get-late-returns.php").then(response => {
        return response.json();
    }
    ).then(data => {
        console.log(data);
        data.forEach(loan => {
            var row = document.createElement("tr");

            var titleCell = document.createElement("td");
            titleCell.textContent = loan.media_title;
            row.appendChild(titleCell);

            var dueDateCell = document.createElement("td");
            dueDateCell.textContent = loan.date_of_return;
            row.appendChild(dueDateCell);

            var feeCell = document.createElement("td");
            feeCell.textContent = loan.fee + " kr";
            row.appendChild(feeCell);

            lateReturnsTableBody.appendChild(row);
        });
    }).catch(error => {
        console.error('Error fetching late returns:', error);
    });


};