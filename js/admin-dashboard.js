document.addEventListener('DOMContentLoaded', function() {
    const mediaEditDialog = document.getElementById("media-edit-dialog");
    const mediaEditForm = document.getElementById("media-edit-form");

    fetch("./php/get-sab-categories.php").then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var SABDropdown = document.getElementById("category");

        data.forEach(category => {
            var option = document.createElement("option");
            option.value = category.signum;
            option.text = category.category;
            SABDropdown.appendChild(option);

            mediaEditForm.categoryEditDialog.appendChild(option.cloneNode(true));
        });
    });

    loadAllMedia();

    fetch("./php/get-users.php").then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var usersTableBody = document.getElementById("users-table-body");

        data.forEach(user => {
            var row = document.createElement("tr");

            var selectionCell = document.createElement("td");
            var checkbox = document.createElement("input");
            checkbox.classList.add("user-checkbox");
            checkbox.type = "checkbox";
            checkbox.value = user.id;
            selectionCell.appendChild(checkbox);
            row.appendChild(selectionCell);

            var usernameCell = document.createElement("td");
            usernameCell.textContent = user.username;
            row.appendChild(usernameCell);

            var isAdmin = document.createElement("td");
            if(user.is_admin) {
                isAdmin.textContent = "Ja";
            } else {
                isAdmin.textContent = "Nej";
            }
            row.appendChild(isAdmin);

            usersTableBody.appendChild(row);
        });
    });

    document.getElementById("add-media").addEventListener('click', () => {
        var signum = document.getElementById("category").value;
        var title = document.getElementById("title").value;
        var author = document.getElementById("author").value;
        var ISBN = document.getElementById("isbn").value;
        var mediaType = document.getElementById("media-type").value;
        var quantity = document.getElementById("quantity").value;
        var price = document.getElementById("price").value;
        var IMDB = document.getElementById("imdb").value;

        if(mediaType == "book") {
            var film = false;
            var audioBook = false;
            var book = true;
        }
        else if(mediaType == "audiobook") {
            var film = false;
            var audioBook = true;
            var book = false;
        }
        else if(mediaType == "film") {
            var film = true;
            var audioBook = false;
            var book = false;
        }
        
        console.log({ signum, title, author, ISBN, film, audioBook, book, price, quantity });
        fetch("./php/add-media.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                SABSignum: signum,
                title: title,
                author: author,
                ISBN: ISBN,
                film: film,
                audioBook: audioBook,
                book: book,
                price: price,
                quantity: quantity,
                IMDB: IMDB
            })
        }).then(response => {
            return response.text();
        }).then(data => {
            console.log(data);
            alert("Media added successfully!");
            loadAllMedia();
        }).catch(error => {
            console.error("Error:", error);
            alert("An error occurred while adding media.");
        });
    });

    const tables = ["available-books-table-body", "available-audiobook-table-body", "available-film-table-body"];
    tables.forEach(tableId => {
        document.getElementById(tableId).addEventListener("change", (e) => {
            if (e.target && e.target.classList.contains("available-media-checkbox")) {
                tables.forEach(id => {
                    document.querySelectorAll(`#${id} .available-media-checkbox`).forEach(otherCheckbox => {
                        if (otherCheckbox !== e.target) {
                            otherCheckbox.checked = false;
                        }
                    });
                });
                console.log("Checkbox clicked:", e.target.value);
            }
        });
    });

    document.getElementById("remove-copy").addEventListener('click', () => {
        var selectedCheckbox = document.querySelector('.available-media-checkbox:checked');
        if(!selectedCheckbox) {
            alert("Vänligen välj en mediekopia att ta bort.");
            return;
        }
        var mediaId = selectedCheckbox.value;

        fetch("./php/delete-copy.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                mediaId: mediaId
            })
        }).then(response => {
            return response.text();
        }).then(data => {
            console.log(data);
            alert("Media copy removed successfully!");
            location.reload();
        }).catch(error => {
            console.error("Error:", error);
            alert("An error occurred while removing media copy.");
        });
    });

    document.getElementById("edit-copy").addEventListener('click', () => {
        var checkboxes = document.querySelectorAll('.available-media-checkbox:checked');
        if(checkboxes.length !== 1) {
            alert("Vänligen välj en mediekopia att redigera.");
            return;
        }

        const mediaId = checkboxes[0].value;

        fetch("./php/get-media.php?id=" + mediaId).then(response => {
            return response.json();
        }).then(data => {
            console.log(data);
            var media = data[0];
            mediaEditForm.categoryEditDialog.value = media.SAB_signum;
            mediaEditForm.titleEditDialog.value = media.title;
            mediaEditForm.authorEditDialog.value = media.author;
            mediaEditForm.priceEditDialog.value = media.price;
            if(media.book) {
                var IMDBInput = document.getElementById("imdbEditDialog");
                var IMDBLabel = document.getElementById("imdbEditDialogLabel");
                IMDBLabel.style.display = "none";
                IMDBInput.style.display = "none";
                mediaEditForm.isbnEditDialog.value = media.ISBN;
                mediaEditForm.mediaTypeEditDialog.value = "book";
            } else if(media.audioBook) {
                mediaEditForm.mediaTypeEditDialog.value = "audiobook";
            } else if(media.film) {
                var isbnInput = document.getElementById("isbnEditDialog");
                var isbnLabel = document.getElementById("isbnEditDialogLabel");
                isbnLabel.style.display = "none";
                isbnInput.style.display = "none";
                mediaEditForm.mediaTypeEditDialog.value = "film";
                mediaEditForm.imdbEditDialog.value = media.IMDB;
            }

        });
        mediaEditDialog.showModal();
    });

    mediaEditDialog.addEventListener("close", (e) => {
        var checkboxes = document.querySelectorAll('.available-media-checkbox:checked');
        var mediaId = checkboxes[0].value;
        e.preventDefault();
        console.log("ye");
        const signum = mediaEditForm.categoryEditDialog.value;
        const title = mediaEditForm.titleEditDialog.value;
        const author = mediaEditForm.authorEditDialog.value;
        const ISBN = mediaEditForm.isbnEditDialog.value;
        const price = mediaEditForm.priceEditDialog.value;
        const mediaType = mediaEditForm.mediaTypeEditDialog.value;
        const IMDB = mediaEditForm.imdbEditDialog.value;

        if(mediaType == "book") {
            var film = false;
            var audioBook = false;
            var book = true;
        }
        else if(mediaType == "audiobook") {
            var film = false;
            var audioBook = true;
            var book = false;
        }
        else if(mediaType == "film") {
            var film = true;
            var audioBook = false;
            var book = false;
        }

        if(mediaEditDialog.returnValue === "submit") {
            fetch("./php/edit-media.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: mediaId,
                    SABSignum: signum,
                    title: title,
                    author: author,
                    ISBN: ISBN,
                    film: film,
                    audioBook: audioBook,
                    book: book,
                    price: price,
                    IMDB: IMDB
                })
            }).then(response => {
                return response.text();
            }).then(data => {
                console.log(data);
                alert("Media edited successfully!");
                loadAllMedia();
            }).catch(error => {
                console.error("Error:", error);
                alert("An error occurred while editing media.");
            });
        }
    });

    document.getElementById("return-media").addEventListener('click', () => {
        var checkboxes = document.querySelectorAll('#unavailable-media-table-body input[type="checkbox"]:checked');
        var mediaIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        fetch("./php/media-return.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                mediaIds: mediaIds
            })
        }).then(response => {
            return response.text();
        }).then(data => {
            console.log(data);
            alert("Media returned successfully!");
            location.reload();
        }).catch(error => {
            console.error("Error:", error);
            alert("An error occurred while returning media.");
        });
    });

    document.querySelectorAll(".menu-item[data-target]").forEach(btn => {
        btn.addEventListener("click", (e) => {
            document.querySelectorAll(".menu-item").forEach(item => item.classList.remove("active"));
            btn.classList.add("active");
          document.body.dataset.section = btn.dataset.target;
        });
    });

    const addUserDialog = document.getElementById("add-user-dialog");
    const userAddForm = document.getElementById("user-add-form");

    document.getElementById("add-user").addEventListener('click', () => {
        addUserDialog.showModal();
    });

    addUserDialog.addEventListener("close", (e) => {
        e.preventDefault();
        const username = userAddForm.username.value;
        const password = userAddForm.password.value;
        const isAdmin = userAddForm.isAdmin.checked;
      
        console.log("Submitted:", { username, password, isAdmin});
      
        
        if(addUserDialog.returnValue === "submit") {
            fetch("./php/add-user.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                    isAdmin: isAdmin
                })
            }).then(response => {
                return response.text();
            }).then(data => {
                console.log(data);
                alert("User added successfully!");
                location.reload();
            }).catch(error => {
                console.error("Error:", error);
                alert("An error occurred while adding user.");
            });
        }
    });

    const userEditForm = document.getElementById("user-edit-form");
    const editUserDialog = document.getElementById("edit-user-dialog");

    document.getElementById("edit-user").addEventListener('click', (e) => {
        const selectedCheckbox = document.querySelector('#users-table-body input[type="checkbox"]:checked');
        if(!selectedCheckbox) {
            alert("Vänligen välj en användare att redigera.");
            return;
        }
        const userId = selectedCheckbox.value;

        fetch("./php/get-users.php?userId=" + userId).then(response => {
            return response.json();
        }).then(data => {
            
            userEditForm.username.value = data.username;
            userEditForm.isAdmin.checked = data.is_admin;
        });
        editUserDialog.dataset.userId = userId;
        editUserDialog.showModal();
    });

    editUserDialog.addEventListener("close", (e) => {
        e.preventDefault();
        const username = userEditForm.username.value;
        const password = userEditForm.password.value;
        const isAdmin = userEditForm.isAdmin.checked;
        const userId = editUserDialog.dataset.userId;
      
        console.log(editUserDialog.returnValue);
        
        if(editUserDialog.returnValue === "submit") {
            console.log("Submitted:", { userId, username, isAdmin});
            fetch("./php/edit-user.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    userId: userId,
                    username: username,
                    password: password,
                    isAdmin: isAdmin
                })
            }).then(response => {
                return response.text();
            }).then(data => {
                console.log(data);
                alert("User edited successfully!");
                location.reload();
            }).catch(error => {
                console.error("Error:", error);
                alert("An error occurred while editing user.");
            });
        }
    });

    document.getElementById("users-table-body").addEventListener("change", (e) => {
        if(e.target && e.target.classList.contains("user-checkbox")) {
            document.querySelectorAll(".user-checkbox").forEach(otherCheckbox => {
                if(otherCheckbox !== e.target) {
                    otherCheckbox.checked = false;
                }
            });
            console.log("Checkbox clicked:", e.target.value);
        }
    });

    document.getElementById("delete-user").addEventListener('click', () => {
        const selectedCheckbox = document.querySelector('#users-table-body input[type="checkbox"]:checked');
        if(!selectedCheckbox) {
            alert("Vänligen välj en användare att ta bort.");
            return;
        }
        const userId = selectedCheckbox.value;

        if(!confirm("Är du säker på att du vill ta bort denna användare?")) {
            return;
        }

        fetch("./php/delete-user.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                userId: userId
            })
        }).then(response => {
            return response.text();
        }).then(data => {
            console.log(data);
            alert("User deleted successfully!");
            location.reload();
        }).catch(error => {
            console.error("Error:", error);
            alert("An error occurred while deleting user.");
        });
    });

    document.getElementById("media-type").addEventListener("change", (e) => {
        var selectedType = e.target.value;
        var imdbInput = document.getElementById("imdb");
        var isbnInput = document.getElementById("isbn");
        var authorInput = document.getElementById("author");

        if(selectedType === "film") {
            isbnInput.style.display = "none";

            authorInput.placeholder = "Regissör";

            imdbInput.style.display = "flex";

        } else if(selectedType === "book") {
            imdbInput.style.display = "none";
            authorInput.placeholder = "Författare";
            isbnInput.style.display = "flex";

        } else if(selectedType === "audiobook") {
            imdbInput.style.display = "none";
            authorInput.placeholder = "Författare";
            isbnInput.style.display = "flex";

        }
    });

});

function loadAllMedia(){
    const lentMediaTable = document.getElementById("unavailable-media-table-body");
    const availableBooksTableBody = document.getElementById("available-books-table-body");
    const availableAudioBooksTableBody = document.getElementById("available-audiobook-table-body");
    const availableFilmsTableBody = document.getElementById("available-film-table-body");

    while (lentMediaTable.rows.length > 1) {
        lentMediaTable.deleteRow(1);
    }

    while (availableBooksTableBody.rows.length > 0) {
        availableBooksTableBody.deleteRow(0);
    }
    while (availableAudioBooksTableBody.rows.length > 0) {
        availableAudioBooksTableBody.deleteRow(0);
    }
    while (availableFilmsTableBody.rows.length > 0) {
        availableFilmsTableBody.deleteRow(0);
    }

    fetch("./php/get-media.php?availableOnly=true&filter=book",).then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var mediaTableBody = document.getElementById("available-books-table-body");

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

            var priceCell = document.createElement("td");
            priceCell.textContent = media.price;
            row.appendChild(priceCell);

            var isbnCell = document.createElement("td");
            isbnCell.textContent = media.ISBN;
            row.appendChild(isbnCell);

            var categoryCell = document.createElement("td");
            categoryCell.textContent = media.SAB_signum;
            row.appendChild(categoryCell);



            mediaTableBody.appendChild(row);
        });
    });

    fetch("./php/get-media.php?availableOnly=true&filter=audiobook",).then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var mediaTableBody = document.getElementById("available-audiobook-table-body");

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

            var priceCell = document.createElement("td");
            priceCell.textContent = media.price;
            row.appendChild(priceCell);

            var isbnCell = document.createElement("td");
            isbnCell.textContent = media.ISBN;
            row.appendChild(isbnCell);

            var categoryCell = document.createElement("td");
            categoryCell.textContent = media.SAB_signum;
            row.appendChild(categoryCell);


            mediaTableBody.appendChild(row);
        });
    });

    fetch("./php/get-media.php?availableOnly=true&filter=film",).then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var mediaTableBody = document.getElementById("available-film-table-body");

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

            var priceCell = document.createElement("td");
            priceCell.textContent = media.price;
            row.appendChild(priceCell);

            var isbnCell = document.createElement("td");
            isbnCell.textContent = media.IMDB;
            row.appendChild(isbnCell);

            var categoryCell = document.createElement("td");
            categoryCell.textContent = media.SAB_signum;
            row.appendChild(categoryCell);

            mediaTableBody.appendChild(row);
        });
    });

    //loaned media
    fetch("./php/get-media.php?availableOnly=false",).then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var mediaTableBody = document.getElementById("unavailable-media-table-body");
        
        if(data.length === 0) {
            var row = document.createElement("tr");
            var noDataCell = document.createElement("td");
            noDataCell.colSpan = 9;
            noDataCell.textContent = "Inget är utlånat.";
            row.appendChild(noDataCell);
            mediaTableBody.appendChild(row);
            return;

        }

        data.forEach(media => {
            var row = document.createElement("tr");

            var selectionCell = document.createElement("td");
            var checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.value = media.m_id;
            selectionCell.appendChild(checkbox);
            row.appendChild(selectionCell);

            var titleCell = document.createElement("td");
            titleCell.textContent = media.title;
            row.appendChild(titleCell);

            var authorCell = document.createElement("td");
            authorCell.textContent = media.author;
            row.appendChild(authorCell);

            var priceCell = document.createElement("td");
            priceCell.textContent = media.price;
            row.appendChild(priceCell);

            var isbnCell = document.createElement("td");
            isbnCell.textContent = media.ISBN;
            row.appendChild(isbnCell);

            var categoryCell = document.createElement("td");
            categoryCell.textContent = media.SAB_signum;
            row.appendChild(categoryCell);

            var mediaTypeCell = document.createElement("td");
            if(media.book) {
                mediaTypeCell.textContent = "Bok";
            } else if(media.audioBook) {
                mediaTypeCell.textContent = "Ljudbok";
            } else if(media.film) {
                mediaTypeCell.textContent = "Film";
            }
            row.appendChild(mediaTypeCell);

            var lentbyCell = document.createElement("td");
            lentbyCell.textContent = media.username;
            row.appendChild(lentbyCell);

            var returnDateCell = document.createElement("td");
            returnDateCell.textContent = media.return_date;
            row.appendChild(returnDateCell);

            mediaTableBody.appendChild(row);
        });
    });

    

}