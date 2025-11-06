document.addEventListener('DOMContentLoaded', function() {
    
    



    loadAllMedia();

    


});




async function loadAllMedia(mediaType = '', searchFor = '', searchTerm = '') {
    console.log(mediaType, searchFor, searchTerm);
    var SABCategories = [];
    await fetch('./php/get-sab-categories.php').then(response => {
        return response.json();
    }).then(data => {
        SABCategories = data;

    }).catch(error => {
        console.error('Error fetching SAB categories:', error);
    });


    var availableMediaTableBody = document.getElementById("media-container");

    let params = new URLSearchParams();
    params.append("availableOnly", "true");

    if (mediaType) params.append("filter", mediaType);
    if (searchFor) params.append("searchFor", searchFor);
    if (searchTerm) params.append("searchTerm", searchTerm);


    let apiCall = "./php/get-media.php?" + params.toString();
    fetch(apiCall).then(response => {
        return response.json();
    }).then(data => {
        console.log(data); 


        data.forEach(media => {
            var itemContainer = document.createElement("li");
            itemContainer.classList.add("media-box", "top-bottom-flex");
            itemContainer.classList.add(media.mediatype);
            //Top container for item
            var topItem = document.createElement("div")

            

            var icon = document.createElement("div");
            icon.classList.add("media-icon");
            if (media.mediatype == "bok") {
                icon.innerHTML = "&#128216";
            } else if (media.mediatype == "ljudbok") {
                icon.innerHTML = "&#127911";
            } else if (media.mediatype == "film") {
                icon.innerHTML = "&#128191";
            }
            topItem.appendChild(icon);

            var titleCell = document.createElement("h2");
            titleCell.textContent = media.title;
            topItem.appendChild(titleCell);
            

            var authorCell = document.createElement("h3");
            authorCell.innerHTML += "Skriven av: ";
            var authorHeader = document.createElement("a");
            authorHeader.textContent = media.author;
            authorCell.appendChild(authorHeader);
            topItem.appendChild(authorCell);

            var typeCell = document.createElement("h4");
            typeCell.textContent = media.mediatype;
            topItem.appendChild(typeCell);






            var categoryCell = document.createElement("div");
            var SABRow = SABCategories.find(cat => cat.signum === media.SAB_signum);
            categoryCell.textContent = SABRow.category;
            topItem.appendChild(categoryCell);



            fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
            .then(response => response.json())
            .then(data => {
                var cellCopiesAvailable = document.createElement("div");
                cellCopiesAvailable.textContent = "";
                data.copies.forEach(copy => {
                    cellCopiesAvailable.textContent += "("+copy.id+"), ";
                });
                topItem.appendChild(cellCopiesAvailable);
            })
            .catch(error => console.error("Error:", error));


            var bottomItem = document.createElement("div");

            var typeCell = document.createElement("h4");
            typeCell.textContent = media.mediatype;


            bottomItem.appendChild(typeCell)
            
            itemContainer.appendChild(topItem);
            itemContainer.appendChild(bottomItem)
            availableMediaTableBody.appendChild(itemContainer);
        });
    }).catch(error => {
        console.error('Error fetching media data:', error);
    });
};