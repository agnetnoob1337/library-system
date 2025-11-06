document.addEventListener('DOMContentLoaded', function() {
    let canEdit = false;
    function startEditTimer() {
        let timeLeft = 10 * 60;

        const timer = setInterval(() => {
            let seconds = timeLeft % 60;

            seconds = seconds < 10 ? "0" + seconds : seconds;

            if (timeLeft <= 0) {
                clearInterval(timer);
                canEdit = false;
            }
            console.log(timeLeft);
            console.log(canEdit);
            timeLeft--;
        }, 1000);
    }

    const mediaEditDialog = document.getElementById("media-edit-dialog");
    const mediaEditForm = document.getElementById("media-edit-form");

    //#region get sab categories
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
    //#endregion

    loadAllMedia();

    //#region get users
    fetch("./php/get-users.php").then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 

        var usersTableBody = document.getElementById("users-table-body");

        data.forEach(user => {
            var row = document.createElement("tr");
            var selectionCell = document.createElement("td");

            var deleteUserButton = document.createElement("button")
            deleteUserButton.value = user.id;
            deleteUserButton.textContent = "Ta bort";
            deleteUserButton.addEventListener("click", function() {
                if (canEdit) {
                    deleteUser(this);
                } else {
                    enterAdminPassword(this);
                }
            });
            //deleteUserButton.addEventListener("click", function() { deleteUser(this); });
            selectionCell.appendChild(deleteUserButton);

            var editUserButton = document.createElement("button")
            editUserButton.value = user.id;
            editUserButton.textContent = "Redigera";
            editUserButton.addEventListener("click", function() {
                if (canEdit) {
                    editUser(this);
                } else {
                    enterAdminPassword(this);
                }
            });
            selectionCell.appendChild(editUserButton);

            row.appendChild(selectionCell);

            var usernameCell = document.createElement("td");
            usernameCell.textContent = user.username;
            row.appendChild(usernameCell);

            var mailCell = document.createElement("td");
            mailCell.textContent = user.mail;
            row.appendChild(mailCell);

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
    //#endregion
    
    //#region add media
    document.getElementById("add-media").addEventListener('click', () => {
        var signum = document.getElementById("category").value;
        var title = document.getElementById("title").value;
        var author = document.getElementById("author").value;
        var ISBN = document.getElementById("isbn").value;
        var mediaType = document.getElementById("media-type").value;
        var quantity = document.getElementById("quantity").value;
        var price = document.getElementById("price").value;
        var IMDB = document.getElementById("imdb").value;

        // if(mediaType == "book") {
        //     var film = false;
        //     var audioBook = false;
        //     var book = true;
        // }
        // else if(mediaType == "audiobook") {
        //     var film = false;
        //     var audioBook = true;
        //     var book = false;
        // }
        // else if(mediaType == "film") {
        //     var film = true;
        //     var audioBook = false;
        //     var book = false;
        // }
        
        console.log({ signum, title, author, ISBN, price, quantity, mediaType });
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
                //film: film,
                //audioBook: audioBook,
                //book: book,
                price: price,
                quantity: quantity,
                IMDB: IMDB,
                mediaType: mediaType
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
    //#endregion

    //#region Adds copies of media
    document.getElementById("add-copy").addEventListener("click", () => {
        var mediaId = document.getElementById("media-id").value;
        var quantityCopy = document.getElementById("quantity-copy").value;

        fetch("./php/add-copy.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                mediaId: mediaId,
                quantityCopy: quantityCopy

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
    //#endregion

    
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

    //#region reomove a copy
    function removeCopy(e) {
        const mediaId = e.value;


        if (confirm('Delete this media copy?')) {

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
        }
    };
   //#endregion

    //#region edit a copy
    function editCopy(e) {
        const mediaId = e.value; // from the "Edit" button
    
        // Store the current mediaId on the dialog element itself
        mediaEditDialog.dataset.mediaId = mediaId;
    
        fetch(`./php/get-media.php?availableOnly=true&id=${mediaId}`)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                const media = data[0];
    
                // Fill form values
                mediaEditForm.categoryEditDialog.value = media.SAB_signum;
                mediaEditForm.titleEditDialog.value = media.title;
                mediaEditForm.authorEditDialog.value = media.author;
                mediaEditForm.priceEditDialog.value = media.price;
                mediaEditForm.mediaTypeEditDialog.value = media.mediatype;
    
                // Reset hidden fields first
                document.getElementById("isbnEditDialog").style.display = "flex";
                document.getElementById("isbnEditDialogLabel").style.display = "flex";
                document.getElementById("imdbEditDialog").style.display = "flex";
                document.getElementById("imdbEditDialogLabel").style.display = "flex";
    
                if (media.mediatype === "bok") {
                    // Hide IMDb fields for books
                    document.getElementById("imdbEditDialog").style.display = "none";
                    document.getElementById("imdbEditDialogLabel").style.display = "none";
                    mediaEditForm.isbnEditDialog.value = media.ISBN;
                } 
                else if (media.mediatype === "ljudbok") {
                    mediaEditForm.isbnEditDialog.value = media.ISBN;
                    document.getElementById("imdbEditDialog").style.display = "none";
                    document.getElementById("imdbEditDialogLabel").style.display = "none";
                } 
                else if (media.mediatype === "film") {
                    // Hide ISBN fields for films
                    document.getElementById("isbnEditDialog").style.display = "none";
                    document.getElementById("isbnEditDialogLabel").style.display = "none";
                    mediaEditForm.imdbEditDialog.value = media.IMDB;
                }
    
                // Open the dialog
                mediaEditDialog.showModal();
            })
            .catch(error => {
                console.error("Error fetching media:", error);
                alert("Could not load media for editing.");
            });
    }

    
    // When dialog closes, save changes if submitted
    mediaEditDialog.addEventListener("close", (e) => {
        e.preventDefault();
    
        if (mediaEditDialog.returnValue !== "submit") return; // only on submit
    
        const mediaId = mediaEditDialog.dataset.mediaId; // get stored ID
        console.log("Editing media with ID:", mediaId);
    
        const signum = mediaEditForm.categoryEditDialog.value;
        const title = mediaEditForm.titleEditDialog.value;
        const author = mediaEditForm.authorEditDialog.value;
        const ISBN = mediaEditForm.isbnEditDialog.value;
        const price = mediaEditForm.priceEditDialog.value;
        const mediaType = mediaEditForm.mediaTypeEditDialog.value;
        const IMDB = mediaEditForm.imdbEditDialog.value;

        console.log({ signum, title, author, ISBN, price, mediaType, IMDB });
    
        let film = false, audioBook = false, book = false;
        if (mediaType === "book") book = true;
        else if (mediaType === "audiobook") audioBook = true;
        else if (mediaType === "film") film = true;
    
        fetch("./php/edit-media.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: mediaId,
                SABSignum: signum,
                title,
                author,
                ISBN,
                film,
                audioBook,
                book,
                price,
                IMDB
            })
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            alert("Media edited successfully!");
            loadAllMedia();
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while editing media.");
        });
    });
    //#endregion
    

    //#region return a media
    document.getElementById("return-media").addEventListener('click', () => {
        //var checkboxes = document.querySelectorAll('#unavailable-media-table-body input[type="checkbox"]:checked');
        var selectedCheckbox = document.querySelector('#unavailable-media-table-body input[type="checkbox"]:checked');
        //var mediaIds = Array.from(checkboxes).map(checkbox => checkbox.value);
        if(selectedCheckbox){
            //var mediaId = Array.from(checkboxes).map(checkbox => Number(checkbox.value));
            //var copyId = Array.from(checkboxes).map(checkbox => Number(checkbox.dataset.copyId));
            var mediaId = selectedCheckbox.value;
            var copyId = selectedCheckbox.dataset.copyId;
            var userId = selectedCheckbox.dataset.userId;
            fetch("./php/media-return.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    mediaId: Number(mediaId), copyId: Number(copyId), userId: Number(userId)
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
        }
    });
    //#endregion
    
    document.querySelectorAll(".menu-item[data-target]").forEach(btn => {
        btn.addEventListener("click", (e) => {
            document.querySelectorAll(".menu-item").forEach(item => item.classList.remove("active"));
            btn.classList.add("active");
          document.body.dataset.section = btn.dataset.target;
        });
    });

    //#region add a user
    const addUserDialog = document.getElementById("add-user-dialog");
    const userAddForm = document.getElementById("user-add-form");
    // deleteUserButton.addEventListener("click", function() {
    //     if (canEdit) {
    //         deleteUser(this);
    //     } else {
    //         enterAdminPassword(this);
    //     }
    // });
    document.getElementById("add-user").addEventListener('click', () => {
        if (canEdit) {
            addUserDialog.showModal();
        } else {
            enterAdminPassword();
        }
    });

    addUserDialog.addEventListener("close", () => {
        const username = userAddForm.username.value;
        const password = userAddForm.password.value;
        const mail = userAddForm.mail.value;
        const isAdmin = userAddForm.isAdmin.checked;
      
        console.log("Submitted:", { username, password, mail, isAdmin});
      
        
        if(addUserDialog.returnValue === "submit") {
            fetch("./php/add-user.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                    isAdmin: isAdmin,
                    mail: mail
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
    //#endregion

    //#region admin enter password to edit

    // function enterAdminPassword(userId) {
    //     const enterAdminPassDialog = document.getElementById("enter-admin-password-dialog");
    //     const enterAdminPassForm = document.getElementById("enter-admin-password-form");

    //     enterAdminPassDialog.showModal();

    //     enterAdminPassForm.onsubmit = async (e) => {
    //         console.log("hej");
    //         e.preventDefault();
    //         const password = enterAdminPassForm.password.value;

    //         try {
    //             const response = await fetch('./php/validate-admin.php', {
    //                 method: 'POST',
    //                 headers: { 'Content-Type': 'application/json' },
    //                 body: JSON.stringify({ password })
    //             });

    //             const result = await response.json();

    //             if(result.success) {
    //                 canEdit = true;
    //                 startEditTimer();
    //                 console.log('Admin verified');
    //                 enterAdminPassDialog.close();
    //                 // editUserDialog.dataset.userId = userId;
    //                 // editUserDialog.showModal();
    //             } else {
    //                 alert('Fel lösenord!');
    //             }

    //         } catch(err) {
    //             console.error('Error validating admin:', err);
    //         }
    //     };
    // }
    function enterAdminPassword(element) {
        const enterAdminPassDialog = document.getElementById("enter-admin-password-dialog");
        const enterAdminPassForm = document.getElementById("enter-admin-password-form");
        
        enterAdminPassDialog.showModal();
    
        enterAdminPassForm.onsubmit = async (e) => {
            e.preventDefault();
            const password = enterAdminPassForm.password.value;
    
            try {
                const response = await fetch('./php/validate-admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password })
                });
    
                const result = await response.json();
    
                if(result.success) {
                    canEdit = true;
                    startEditTimer();
                    console.log('Admin verified');
                    enterAdminPassDialog.close();
    
                } else {
                    alert('Fel lösenord!');
                }
    
            } catch(err) {
                console.error('Error validating admin:', err);
            }
        };
    }
    //#endregion
    
    const userEditForm = document.getElementById("user-edit-form");
    const editUserDialog = document.getElementById("edit-user-dialog");


    //#region edit a user
    function editUser(e) {
        const userId = e.value;


        fetch("./php/get-users.php?userId=" + userId).then(response => {
            return response.json();
        }).then(data => {
            
            userEditForm.username.value = data.username;
            userEditForm.isAdmin.checked = data.is_admin;
        });
        editUserDialog.dataset.userId = userId;
        editUserDialog.showModal();
    };
      //#endregion


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


      //#region delete a user
    function deleteUser(e) {
        const userId = e.value;

        if(!confirm("Är du säker på att du vill ta bort denna användare? Detta är permanent och kan inte återkallas!")) {
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

    };
      //#endregion


    //#region media type styling
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
    //#endregion 


    //#region trigger loads after admin inputs
    document.getElementById("search-for-book").addEventListener("change", triggerMediaLoadBook);
    document.getElementById("search-input-book").addEventListener("input", triggerMediaLoadBook);
    function triggerMediaLoadBook() {
        const searchForWord = document.getElementById("search-for-book").value;
        const searchTerm = document.getElementById("search-input-book").value;
        const selectedType = "book";
        loadAllMedia(selectedType, searchForWord, searchTerm);
    }
    document.getElementById("search-for-audiobook").addEventListener("change", triggerMediaLoadAudiobook);
    document.getElementById("search-input-audiobook").addEventListener("input", triggerMediaLoadAudiobook);
    function triggerMediaLoadAudiobook() {
        const searchForWord = document.getElementById("search-for-audiobook").value;
        const searchTerm = document.getElementById("search-input-audiobook").value;
        const selectedType = "audiobook";
        loadAllMedia(selectedType, searchForWord, searchTerm);
    }
    document.getElementById("search-for-movie").addEventListener("change", triggerMediaLoadMovie);
    document.getElementById("search-input-movie").addEventListener("input", triggerMediaLoadMovie);
    function triggerMediaLoadMovie() {
        const searchForWord = document.getElementById("search-for-movie").value;
        const searchTerm = document.getElementById("search-input-movie").value;
        const selectedType = "movie";
        loadAllMedia(selectedType, searchForWord, searchTerm);
    }
    //#endregion

    //#region handle search input for admin
    document.getElementById("search-input-book").addEventListener("input", function() {
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
    document.getElementById("search-input-audiobook").addEventListener("input", function() {
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
    document.getElementById("search-input-movie").addEventListener("input", function() {
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
    //#endregion
       
async function loadAllMedia(mediaType = '', searchFor = '', searchTerm = ''){

    if(searchTerm){
          console.log(mediaType, searchFor, searchTerm);
          var SABCategories = [];
          await fetch('./php/get-sab-categories.php').then(response => {
              return response.json();
          }).then(data => {
              SABCategories = data;
  
          }).catch(error => {
              console.error('Error fetching SAB categories:', error);
          });
  
          //#region empty the available bodies to not duplicate media
          var availableBookTableBody = document.getElementById("available-books-table-body");
          if (!availableBookTableBody) {
              console.warn("Table body missing — page may not have media tables");
          } else {
              availableBookTableBody.innerHTML = "";
          }
  
          var availableAudiobookTableBody = document.getElementById("available-audiobook-table-body");
          if (!availableAudiobookTableBody) {
              console.warn("Table body missing — page may not have media tables");
          } else {
              availableAudiobookTableBody.innerHTML = "";
          }
  
          var availableMovieTableBody = document.getElementById("available-film-table-body");
          if (!availableMovieTableBody) {
              console.warn("Table body missing — page may not have media tables");
          } else {
              availableMovieTableBody.innerHTML = "";
          }
          //#endregion
  
          //#region get the filter parameters
          let filterParam = mediaType;
          if(mediaType === "book") filterParam = "bok";
          else if(mediaType === "audiobook") filterParam = "ljudbok";
          else if(mediaType === "movie") filterParam = "film";
          //#endregion
          fetch(`./php/get-media.php?availableOnly=true&filter=${filterParam}&searchTerm=${searchTerm}&searchFor=${searchFor}`).then(response => {
              return response.json();
              // console.log("Response status:", response.status);
              // return response.text(); // byt till text för att se RÅ output
          }).then(data => {
      
              if(mediaType == "book"){
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
          
                      var mediaIDCell = document.createElement("td");
                      mediaIDCell.textContent = media.id;
                      row.appendChild(mediaIDCell);
          
                      fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
                      .then(response => response.json())
                      .then(data => {
                          var cellCopiesAvailable = document.createElement("td");
                          cellCopiesAvailable.textContent = "";
                          data.copies.forEach(copy => {
                              cellCopiesAvailable.textContent += "("+copy.id+"), ";
                          });
                          row.appendChild(cellCopiesAvailable);
                      })
                      .catch(error => console.error("Error:", error));
          
                      mediaTableBody.appendChild(row);
                  });
              }
              else if(mediaType == "audiobook"){
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
          
                      var mediaIDCell = document.createElement("td");
                      mediaIDCell.textContent = media.id;
                      row.appendChild(mediaIDCell);
          
                      fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
                      .then(response => response.json())
                      .then(data => {
                          var cellCopiesAvailable = document.createElement("td");
                          cellCopiesAvailable.textContent = "";
                          data.copies.forEach(copy => {
                              cellCopiesAvailable.textContent += "("+copy.id+"), ";
                          });
                          row.appendChild(cellCopiesAvailable);
                      })
                      .catch(error => console.error("Error:", error));
          
                      mediaTableBody.appendChild(row);
                  });
              }
              else if(mediaType == "movie"){
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
          
                      var mediaIDCell = document.createElement("td");
                      mediaIDCell.textContent = media.id;
                      row.appendChild(mediaIDCell);
          
                      fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
                      .then(response => response.json())
                      .then(data => {
                          var cellCopiesAvailable = document.createElement("td");
                          cellCopiesAvailable.textContent = "";
                          data.copies.forEach(copy => {
                              cellCopiesAvailable.textContent += "("+copy.id+"), ";
                          });
                          row.appendChild(cellCopiesAvailable);
                      })
                      .catch(error => console.error("Error:", error));
          
                      mediaTableBody.appendChild(row);
                  });
              }
          });
          //#endregion
          //return;
      }
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
  
      //#region get available books
      if(mediaType != "book" || searchTerm.length == 0){
          fetch("./php/get-media.php?availableOnly=true&filter=bok",).then(response => {
              return response.json();
              // console.log("Response status:", response.status);
              // return response.text(); // byt till text för att se RÅ output
          }).then(data => {
              //console.log(data);
  
  
              var mediaTableBody = document.getElementById("available-books-table-body");
  
  
          data.forEach(media => {
            var row = document.createElement("tr");
            var selectionCell = document.createElement("td");
  
            var deleteCopyButton = document.createElement("button");
            deleteCopyButton.textContent = "Ta bort";
            deleteCopyButton.value = media.id;
            // deleteCopyButton.addEventListener("click", function(e) { 
            //     removeCopy(e.target);
            // });
            deleteCopyButton.addEventListener("click", function(e) {
                if (canEdit) {
                    removeCopy(e.target);
                } else {
                    enterAdminPassword(e.target);
                }
            });
              selectionCell.appendChild(deleteCopyButton);
  
              var editCopyButton = document.createElement("button");
              editCopyButton.textContent = "Redigera";
              editCopyButton.value = media.id;
              editCopyButton.addEventListener("click", function(e) { 
                  if (canEdit) {
                    editCopy(e.target);
                } else {
                    enterAdminPassword(e.target);
                }
              });
              selectionCell.appendChild(editCopyButton);
  
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
  
                  var mediaIDCell = document.createElement("td");
                  mediaIDCell.textContent = media.id;
                  row.appendChild(mediaIDCell);
  
                  fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
                  .then(response => response.json())
                  .then(data => {
                      var cellCopiesAvailable = document.createElement("td");
                      cellCopiesAvailable.textContent = "";
                      data.copies.forEach(copy => {
                          cellCopiesAvailable.textContent += "("+copy.id+"), ";
                      });
                      row.appendChild(cellCopiesAvailable);
                  })
                  .catch(error => console.error("Error:", error));
  
  
  
                  mediaTableBody.appendChild(row);
              });
          });
      }
      //#endregion
  
      //#region get available audiobooks
      if(mediaType != "audiobook" || searchTerm.length == 0){
          fetch("./php/get-media.php?availableOnly=true&filter=ljudbok",).then(response => {
              return response.json();
          }).then(data => {
              console.log(data); 
  
              var mediaTableBody = document.getElementById("available-audiobook-table-body");
  
          data.forEach(media => {
              var row = document.createElement("tr");
              var selectionCell = document.createElement("td");
  
              var deleteCopyButton = document.createElement("button");
              deleteCopyButton.textContent = "Ta bort";
              deleteCopyButton.value = media.id;
            //   deleteCopyButton.addEventListener("click", function(e) { 
            //       removeCopy(e.target);
            //   });
            deleteCopyButton.addEventListener("click", function(e) {
                if (canEdit) {
                    removeCopy(e.target);
                } else {
                    enterAdminPassword(e.target);
                }
            });
              selectionCell.appendChild(deleteCopyButton);
  
              var editCopyButton = document.createElement("button");
              editCopyButton.textContent = "Redigera";
              editCopyButton.value = media.id;
            //   editCopyButton.addEventListener("click", function(e) { 
            //       editCopy(e.target);
            //   });
            editCopyButton.addEventListener("click", function(e) { 
                if (canEdit) {
                  editCopy(e.target);
              } else {
                  enterAdminPassword(e.target);
              }
            });
              selectionCell.appendChild(editCopyButton);
  
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
  
                  var mediaIDCell = document.createElement("td");
                  mediaIDCell.textContent = media.id;
                  row.appendChild(mediaIDCell);
  
                  fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
                  .then(response => response.json())
                  .then(data => {
                      var cellCopiesAvailable = document.createElement("td");
                      cellCopiesAvailable.textContent = "";
                      data.copies.forEach(copy => {
                          cellCopiesAvailable.textContent += "("+copy.id+"), ";
                      });
                      row.appendChild(cellCopiesAvailable);
                  })
                  .catch(error => console.error("Error:", error));
  
                  mediaTableBody.appendChild(row);
              });
          });
      }
      //#endregion
  
      //#region get available movies
      if(mediaType != "movie" || searchTerm.length == 0){
          fetch("./php/get-media.php?availableOnly=true&filter=film",).then(response => {
              return response.json();
          }).then(data => {
              console.log(data); 
  
              var mediaTableBody = document.getElementById("available-film-table-body");
  
          data.forEach(media => {
              var row = document.createElement("tr");
              var selectionCell = document.createElement("td");
  
              var deleteCopyButton = document.createElement("button");
              deleteCopyButton.textContent = "Ta bort";
              deleteCopyButton.value = media.id;
            //   deleteCopyButton.addEventListener("click", function(e) { 
            //       removeCopy(e.target);
            //   });
            deleteCopyButton.addEventListener("click", function(e) {
                if (canEdit) {
                    removeCopy(e.target);
                } else {
                    enterAdminPassword(e.target);
                }
            });
              selectionCell.appendChild(deleteCopyButton);
  
              var editCopyButton = document.createElement("button");
              editCopyButton.textContent = "Redigera";
              editCopyButton.value = media.id;
            //   editCopyButton.addEventListener("click", function(e) { 
            //       editCopy(e.target);
            //   });
            editCopyButton.addEventListener("click", function(e) { 
                if (canEdit) {
                  editCopy(e.target);
              } else {
                  enterAdminPassword(e.target);
              }
            });
              selectionCell.appendChild(editCopyButton);
  
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
  
                  var mediaIDCell = document.createElement("td");
                  mediaIDCell.textContent = media.id;
                  row.appendChild(mediaIDCell);
  
                  fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
                  .then(response => response.json())
                  .then(data => {
                      var cellCopiesAvailable = document.createElement("td");
                      cellCopiesAvailable.textContent = "";
                      data.copies.forEach(copy => {
                          cellCopiesAvailable.textContent += "("+copy.id+"), ";
                      });
                      row.appendChild(cellCopiesAvailable);
                  })
                  .catch(error => console.error("Error:", error));
  
                  mediaTableBody.appendChild(row);
              });
          });
      }
      //#endregion
  
      //#region get loaned media
      fetch("./php/get-media.php?availableOnly=false",).then(response => {
          return response.json();
      }).then(data => {
          console.log(data); 
  
          var mediaTableBody = document.getElementById("unavailable-media-table-body");
          
          if(data.length === 0) {
              var row = document.createElement("tr");
              var noDataCell = document.createElement("td");
              noDataCell.colSpan = 10;
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
              checkbox.value = media.media_id;
              checkbox.dataset.copyId = media.copy_id;
              checkbox.dataset.userId = media.user_id;
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
              mediaTypeCell.textContent = media.mediatype;
              // if(media.book) {
              //     mediaTypeCell.textContent = "Bok";
              // } else if(media.audioBook) {
              //     mediaTypeCell.textContent = "Ljudbok";
              // } else if(media.film) {
              //     mediaTypeCell.textContent = "Film";
              // }
              row.appendChild(mediaTypeCell);
  
              var lentbyCell = document.createElement("td");
              lentbyCell.textContent = media.username;
              row.appendChild(lentbyCell);
  
              var returnDateCell = document.createElement("td");
              returnDateCell.textContent = media.return_date;
              row.appendChild(returnDateCell);
  
              var copyIDCell = document.createElement("td");
              copyIDCell.textContent = media.copy_id;
              row.appendChild(copyIDCell);
  
              mediaTableBody.appendChild(row);
          });
      });
      //#endregion
  
      
  }
  
  
  
});


    
// async function loadAllMedia(mediaType = '', searchFor = '', searchTerm = ''){

//   if(searchTerm){
//         console.log(mediaType, searchFor, searchTerm);
//         var SABCategories = [];
//         await fetch('./php/get-sab-categories.php').then(response => {
//             return response.json();
//         }).then(data => {
//             SABCategories = data;

//         }).catch(error => {
//             console.error('Error fetching SAB categories:', error);
//         });

//         //#region empty the available bodies to not duplicate media
//         var availableBookTableBody = document.getElementById("available-books-table-body");
//         if (!availableBookTableBody) {
//             console.warn("Table body missing — page may not have media tables");
//         } else {
//             availableBookTableBody.innerHTML = "";
//         }

//         var availableAudiobookTableBody = document.getElementById("available-audiobook-table-body");
//         if (!availableAudiobookTableBody) {
//             console.warn("Table body missing — page may not have media tables");
//         } else {
//             availableAudiobookTableBody.innerHTML = "";
//         }

//         var availableMovieTableBody = document.getElementById("available-film-table-body");
//         if (!availableMovieTableBody) {
//             console.warn("Table body missing — page may not have media tables");
//         } else {
//             availableMovieTableBody.innerHTML = "";
//         }
//         //#endregion

//         //#region get the filter parameters
//         let filterParam = mediaType;
//         if(mediaType === "book") filterParam = "bok";
//         else if(mediaType === "audiobook") filterParam = "ljudbok";
//         else if(mediaType === "movie") filterParam = "film";
//         //#endregion
//         fetch(`./php/get-media.php?availableOnly=true&filter=${filterParam}&searchTerm=${searchTerm}&searchFor=${searchFor}`).then(response => {
//             return response.json();
//             // console.log("Response status:", response.status);
//             // return response.text(); // byt till text för att se RÅ output
//         }).then(data => {
    
//             if(mediaType == "book"){
//                 var mediaTableBody = document.getElementById("available-books-table-body");
    
//                 data.forEach(media => {
//                     var row = document.createElement("tr");
        
//                     var selectionCell = document.createElement("td");
//                     var checkbox = document.createElement("input");
//                     checkbox.type = "checkbox";
//                     checkbox.classList.add("available-media-checkbox");
//                     checkbox.value = media.id;
//                     selectionCell.appendChild(checkbox);
//                     row.appendChild(selectionCell);
        
        
//                     var titleCell = document.createElement("td");
//                     titleCell.textContent = media.title;
//                     row.appendChild(titleCell);
        
//                     var authorCell = document.createElement("td");
//                     authorCell.textContent = media.author;
//                     row.appendChild(authorCell);
        
//                     var priceCell = document.createElement("td");
//                     priceCell.textContent = media.price;
//                     row.appendChild(priceCell);
        
//                     var isbnCell = document.createElement("td");
//                     isbnCell.textContent = media.ISBN;
//                     row.appendChild(isbnCell);
        
//                     var categoryCell = document.createElement("td");
//                     categoryCell.textContent = media.SAB_signum;
//                     row.appendChild(categoryCell);
        
//                     var mediaIDCell = document.createElement("td");
//                     mediaIDCell.textContent = media.id;
//                     row.appendChild(mediaIDCell);
        
//                     fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
//                     .then(response => response.json())
//                     .then(data => {
//                         var cellCopiesAvailable = document.createElement("td");
//                         cellCopiesAvailable.textContent = "";
//                         data.copies.forEach(copy => {
//                             cellCopiesAvailable.textContent += "("+copy.id+"), ";
//                         });
//                         row.appendChild(cellCopiesAvailable);
//                     })
//                     .catch(error => console.error("Error:", error));
        
//                     mediaTableBody.appendChild(row);
//                 });
//             }
//             else if(mediaType == "audiobook"){
//                 var mediaTableBody = document.getElementById("available-audiobook-table-body");

//                 data.forEach(media => {
//                     var row = document.createElement("tr");

//                     var selectionCell = document.createElement("td");
//                     var checkbox = document.createElement("input");
//                     checkbox.type = "checkbox";
//                     checkbox.classList.add("available-media-checkbox");
//                     checkbox.value = media.id;
//                     selectionCell.appendChild(checkbox);
//                     row.appendChild(selectionCell);
        
        
//                     var titleCell = document.createElement("td");
//                     titleCell.textContent = media.title;
//                     row.appendChild(titleCell);
        
//                     var authorCell = document.createElement("td");
//                     authorCell.textContent = media.author;
//                     row.appendChild(authorCell);
        
//                     var priceCell = document.createElement("td");
//                     priceCell.textContent = media.price;
//                     row.appendChild(priceCell);
        
//                     var isbnCell = document.createElement("td");
//                     isbnCell.textContent = media.ISBN;
//                     row.appendChild(isbnCell);
        
//                     var categoryCell = document.createElement("td");
//                     categoryCell.textContent = media.SAB_signum;
//                     row.appendChild(categoryCell);
        
//                     var mediaIDCell = document.createElement("td");
//                     mediaIDCell.textContent = media.id;
//                     row.appendChild(mediaIDCell);
        
//                     fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
//                     .then(response => response.json())
//                     .then(data => {
//                         var cellCopiesAvailable = document.createElement("td");
//                         cellCopiesAvailable.textContent = "";
//                         data.copies.forEach(copy => {
//                             cellCopiesAvailable.textContent += "("+copy.id+"), ";
//                         });
//                         row.appendChild(cellCopiesAvailable);
//                     })
//                     .catch(error => console.error("Error:", error));
        
//                     mediaTableBody.appendChild(row);
//                 });
//             }
//             else if(mediaType == "movie"){
//                 var mediaTableBody = document.getElementById("available-film-table-body");

//                 data.forEach(media => {
//                     var row = document.createElement("tr");
        
//                     var selectionCell = document.createElement("td");
//                     var checkbox = document.createElement("input");
//                     checkbox.type = "checkbox";
//                     checkbox.classList.add("available-media-checkbox");
//                     checkbox.value = media.id;
//                     selectionCell.appendChild(checkbox);
//                     row.appendChild(selectionCell);
        
        
//                     var titleCell = document.createElement("td");
//                     titleCell.textContent = media.title;
//                     row.appendChild(titleCell);
        
//                     var authorCell = document.createElement("td");
//                     authorCell.textContent = media.author;
//                     row.appendChild(authorCell);
        
//                     var priceCell = document.createElement("td");
//                     priceCell.textContent = media.price;
//                     row.appendChild(priceCell);
        
//                     var isbnCell = document.createElement("td");
//                     isbnCell.textContent = media.IMDB;
//                     row.appendChild(isbnCell);
        
//                     var categoryCell = document.createElement("td");
//                     categoryCell.textContent = media.SAB_signum;
//                     row.appendChild(categoryCell);
        
//                     var mediaIDCell = document.createElement("td");
//                     mediaIDCell.textContent = media.id;
//                     row.appendChild(mediaIDCell);
        
//                     fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
//                     .then(response => response.json())
//                     .then(data => {
//                         var cellCopiesAvailable = document.createElement("td");
//                         cellCopiesAvailable.textContent = "";
//                         data.copies.forEach(copy => {
//                             cellCopiesAvailable.textContent += "("+copy.id+"), ";
//                         });
//                         row.appendChild(cellCopiesAvailable);
//                     })
//                     .catch(error => console.error("Error:", error));
        
//                     mediaTableBody.appendChild(row);
//                 });
//             }
//         });
//         //#endregion
//         //return;
//     }
//     const lentMediaTable = document.getElementById("unavailable-media-table-body");
//     const availableBooksTableBody = document.getElementById("available-books-table-body");
//     const availableAudioBooksTableBody = document.getElementById("available-audiobook-table-body");
//     const availableFilmsTableBody = document.getElementById("available-film-table-body");

//     while (lentMediaTable.rows.length > 1) {
//         lentMediaTable.deleteRow(1);
//     }

//     while (availableBooksTableBody.rows.length > 0) {
//         availableBooksTableBody.deleteRow(0);
//     }
//     while (availableAudioBooksTableBody.rows.length > 0) {
//         availableAudioBooksTableBody.deleteRow(0);
//     }
//     while (availableFilmsTableBody.rows.length > 0) {
//         availableFilmsTableBody.deleteRow(0);
//     }

//     //#region get available books
//     if(mediaType != "book" || searchTerm.length == 0){
//         fetch("./php/get-media.php?availableOnly=true&filter=bok",).then(response => {
//             return response.json();
//             // console.log("Response status:", response.status);
//             // return response.text(); // byt till text för att se RÅ output
//         }).then(data => {
//             //console.log(data);


//             var mediaTableBody = document.getElementById("available-books-table-body");


//         data.forEach(media => {
//             var row = document.createElement("tr");
//             var selectionCell = document.createElement("td");

//             var deleteCopyButton = document.createElement("button");
//             deleteCopyButton.textContent = "Ta bort";
//             deleteCopyButton.value = media.id;
//             // deleteCopyButton.addEventListener("click", function(e) { 
//             //     removeCopy(e.target);
//             // });
//             deleteCopyButton.addEventListener("click", function() {
//                 if (canEdit) {
//                     removeCopy(e.target);
//                 } else {
//                     enterAdminPassword(this);
//                 }
//             });
//             selectionCell.appendChild(deleteCopyButton);

//             var editCopyButton = document.createElement("button");
//             editCopyButton.textContent = "Redigera";
//             editCopyButton.value = media.id;
//             editCopyButton.addEventListener("click", function(e) { 
//                 editCopy(e.target);
//             });
//             selectionCell.appendChild(editCopyButton);

//             row.appendChild(selectionCell);

//                 var titleCell = document.createElement("td");
//                 titleCell.textContent = media.title;
//                 row.appendChild(titleCell);

//                 var authorCell = document.createElement("td");
//                 authorCell.textContent = media.author;
//                 row.appendChild(authorCell);

//                 var priceCell = document.createElement("td");
//                 priceCell.textContent = media.price;
//                 row.appendChild(priceCell);

//                 var isbnCell = document.createElement("td");
//                 isbnCell.textContent = media.ISBN;
//                 row.appendChild(isbnCell);

//                 var categoryCell = document.createElement("td");
//                 categoryCell.textContent = media.SAB_signum;
//                 row.appendChild(categoryCell);

//                 var mediaIDCell = document.createElement("td");
//                 mediaIDCell.textContent = media.id;
//                 row.appendChild(mediaIDCell);

//                 fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
//                 .then(response => response.json())
//                 .then(data => {
//                     var cellCopiesAvailable = document.createElement("td");
//                     cellCopiesAvailable.textContent = "";
//                     data.copies.forEach(copy => {
//                         cellCopiesAvailable.textContent += "("+copy.id+"), ";
//                     });
//                     row.appendChild(cellCopiesAvailable);
//                 })
//                 .catch(error => console.error("Error:", error));



//                 mediaTableBody.appendChild(row);
//             });
//         });
//     }
//     //#endregion

//     //#region get available audiobooks
//     if(mediaType != "audiobook" || searchTerm.length == 0){
//         fetch("./php/get-media.php?availableOnly=true&filter=ljudbok",).then(response => {
//             return response.json();
//         }).then(data => {
//             console.log(data); 

//             var mediaTableBody = document.getElementById("available-audiobook-table-body");

//         data.forEach(media => {
//             var row = document.createElement("tr");
//             var selectionCell = document.createElement("td");

//             var deleteCopyButton = document.createElement("button");
//             deleteCopyButton.textContent = "Ta bort";
//             deleteCopyButton.value = media.id;
//             deleteCopyButton.addEventListener("click", function(e) { 
//                 removeCopy(e.target);
//             });
//             selectionCell.appendChild(deleteCopyButton);

//             var editCopyButton = document.createElement("button");
//             editCopyButton.textContent = "Redigera";
//             editCopyButton.value = media.id;
//             editCopyButton.addEventListener("click", function(e) { 
//                 editCopy(e.target);
//             });
//             selectionCell.appendChild(editCopyButton);

//             row.appendChild(selectionCell);


//                 var titleCell = document.createElement("td");
//                 titleCell.textContent = media.title;
//                 row.appendChild(titleCell);

//                 var authorCell = document.createElement("td");
//                 authorCell.textContent = media.author;
//                 row.appendChild(authorCell);

//                 var priceCell = document.createElement("td");
//                 priceCell.textContent = media.price;
//                 row.appendChild(priceCell);

//                 var isbnCell = document.createElement("td");
//                 isbnCell.textContent = media.ISBN;
//                 row.appendChild(isbnCell);

//                 var categoryCell = document.createElement("td");
//                 categoryCell.textContent = media.SAB_signum;
//                 row.appendChild(categoryCell);

//                 var mediaIDCell = document.createElement("td");
//                 mediaIDCell.textContent = media.id;
//                 row.appendChild(mediaIDCell);

//                 fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
//                 .then(response => response.json())
//                 .then(data => {
//                     var cellCopiesAvailable = document.createElement("td");
//                     cellCopiesAvailable.textContent = "";
//                     data.copies.forEach(copy => {
//                         cellCopiesAvailable.textContent += "("+copy.id+"), ";
//                     });
//                     row.appendChild(cellCopiesAvailable);
//                 })
//                 .catch(error => console.error("Error:", error));

//                 mediaTableBody.appendChild(row);
//             });
//         });
//     }
//     //#endregion

//     //#region get available movies
//     if(mediaType != "movie" || searchTerm.length == 0){
//         fetch("./php/get-media.php?availableOnly=true&filter=film",).then(response => {
//             return response.json();
//         }).then(data => {
//             console.log(data); 

//             var mediaTableBody = document.getElementById("available-film-table-body");

//         data.forEach(media => {
//             var row = document.createElement("tr");
//             var selectionCell = document.createElement("td");

//             var deleteCopyButton = document.createElement("button");
//             deleteCopyButton.textContent = "Ta bort";
//             deleteCopyButton.value = media.id;
//             deleteCopyButton.addEventListener("click", function(e) { 
//                 removeCopy(e.target);
//             });
//             selectionCell.appendChild(deleteCopyButton);

//             var editCopyButton = document.createElement("button");
//             editCopyButton.textContent = "Redigera";
//             editCopyButton.value = media.id;
//             editCopyButton.addEventListener("click", function(e) { 
//                 editCopy(e.target);
//             });
//             selectionCell.appendChild(editCopyButton);

//             row.appendChild(selectionCell);


//                 var titleCell = document.createElement("td");
//                 titleCell.textContent = media.title;
//                 row.appendChild(titleCell);

//                 var authorCell = document.createElement("td");
//                 authorCell.textContent = media.author;
//                 row.appendChild(authorCell);

//                 var priceCell = document.createElement("td");
//                 priceCell.textContent = media.price;
//                 row.appendChild(priceCell);

//                 var isbnCell = document.createElement("td");
//                 isbnCell.textContent = media.IMDB;
//                 row.appendChild(isbnCell);

//                 var categoryCell = document.createElement("td");
//                 categoryCell.textContent = media.SAB_signum;
//                 row.appendChild(categoryCell);

//                 var mediaIDCell = document.createElement("td");
//                 mediaIDCell.textContent = media.id;
//                 row.appendChild(mediaIDCell);

//                 fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
//                 .then(response => response.json())
//                 .then(data => {
//                     var cellCopiesAvailable = document.createElement("td");
//                     cellCopiesAvailable.textContent = "";
//                     data.copies.forEach(copy => {
//                         cellCopiesAvailable.textContent += "("+copy.id+"), ";
//                     });
//                     row.appendChild(cellCopiesAvailable);
//                 })
//                 .catch(error => console.error("Error:", error));

//                 mediaTableBody.appendChild(row);
//             });
//         });
//     }
//     //#endregion

//     //#region get loaned media
//     fetch("./php/get-media.php?availableOnly=false",).then(response => {
//         return response.json();
//     }).then(data => {
//         console.log(data); 

//         var mediaTableBody = document.getElementById("unavailable-media-table-body");
        
//         if(data.length === 0) {
//             var row = document.createElement("tr");
//             var noDataCell = document.createElement("td");
//             noDataCell.colSpan = 10;
//             noDataCell.textContent = "Inget är utlånat.";
//             row.appendChild(noDataCell);
//             mediaTableBody.appendChild(row);
//             return;

//         }

//         data.forEach(media => {
//             var row = document.createElement("tr");

//             var selectionCell = document.createElement("td");
//             var checkbox = document.createElement("input");
//             checkbox.type = "checkbox";
//             checkbox.value = media.media_id;
//             checkbox.dataset.copyId = media.copy_id;
//             checkbox.dataset.userId = media.user_id;
//             selectionCell.appendChild(checkbox);
//             row.appendChild(selectionCell);

//             var titleCell = document.createElement("td");
//             titleCell.textContent = media.title;
//             row.appendChild(titleCell);

//             var authorCell = document.createElement("td");
//             authorCell.textContent = media.author;
//             row.appendChild(authorCell);

//             var priceCell = document.createElement("td");
//             priceCell.textContent = media.price;
//             row.appendChild(priceCell);

//             var isbnCell = document.createElement("td");
//             isbnCell.textContent = media.ISBN;
//             row.appendChild(isbnCell);

//             var categoryCell = document.createElement("td");
//             categoryCell.textContent = media.SAB_signum;
//             row.appendChild(categoryCell);

//             var mediaTypeCell = document.createElement("td");
//             mediaTypeCell.textContent = media.mediatype;
//             // if(media.book) {
//             //     mediaTypeCell.textContent = "Bok";
//             // } else if(media.audioBook) {
//             //     mediaTypeCell.textContent = "Ljudbok";
//             // } else if(media.film) {
//             //     mediaTypeCell.textContent = "Film";
//             // }
//             row.appendChild(mediaTypeCell);

//             var lentbyCell = document.createElement("td");
//             lentbyCell.textContent = media.username;
//             row.appendChild(lentbyCell);

//             var returnDateCell = document.createElement("td");
//             returnDateCell.textContent = media.return_date;
//             row.appendChild(returnDateCell);

//             var copyIDCell = document.createElement("td");
//             copyIDCell.textContent = media.copy_id;
//             row.appendChild(copyIDCell);

//             mediaTableBody.appendChild(row);
//         });
//     });
//     //#endregion

    
// }


