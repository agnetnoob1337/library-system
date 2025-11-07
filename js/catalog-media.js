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
    availableMediaTableBody.innerHTML = "";

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


            var gridItem = document.createElement("div")
            gridItem.classList.add("media-grid")

            var textItem = document.createElement("div")
            var iconItem = document.createElement("div")
            var bottomTextItem = document.createElement("div")
            var loanItem = document.createElement("div")


            var titleItem = document.createElement("div")
            var descriptionItem = document.createElement("div")




            var titleCell = document.createElement("h2");
            titleCell.textContent = media.title;

            var authorCell = document.createElement("h3");
            authorCell.innerHTML += "Skriven av: ";
            var authorHeader = document.createElement("a");
            authorHeader.textContent = media.author;
            authorCell.appendChild(authorHeader);

            var textLine = document.createTextNode(" | ")
            

            
            var categoryCell = document.createElement("div");
            var SABRow = SABCategories.find(cat => cat.signum === media.SAB_signum);
            categoryCell.textContent = SABRow.category;


            titleItem.appendChild(titleCell)
            descriptionItem.appendChild(authorCell)
            descriptionItem.appendChild(authorHeader)
            descriptionItem.appendChild(textLine)

            descriptionItem.appendChild(categoryCell)
            textItem.appendChild(titleItem)
            textItem.appendChild(descriptionItem)

            textItem.classList.add("text-div")


            var icon = document.createElement("div");
            icon.classList.add("media-icon");
            if (media.mediatype == "bok") {
                icon.innerHTML = "&#128216";
            } else if (media.mediatype == "ljudbok") {
                icon.innerHTML = "&#127911";
            } else if (media.mediatype == "film") {
                icon.innerHTML = "&#128191";
            }
            iconItem.classList.add("catalog-text-icon")
            iconItem.appendChild(icon)

            var bottomItem = document.createElement("div");
            var typeCell = document.createElement("h4");
            typeCell.textContent = media.mediatype;
            bottomTextItem.appendChild(typeCell)

            var loanButton = document.createElement("button")
            loanButton.classList.add("loan-button")
            loanButton.appendChild(document.createTextNode("Boeeow"))

            // Loan button event listener
            loanButton.addEventListener("click", function() {
                var mediaId = media.id; // use the current media's ID
                fetch(`./php/media-checkout.php?mediaId=${mediaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ mediaId: mediaId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Media utlånat!");
                        loadAllMedia(); // reload updated list
                    } else {
                        alert("Fel vid utlåning: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error during checkout:", error);
                    alert("Ett fel uppstod vid utlåning.");
                });
            });


            
            loanItem.classList.add("grid-view-button")
            loanItem.appendChild(loanButton)
            




            gridItem.appendChild(textItem)
            gridItem.appendChild(iconItem)
            gridItem.appendChild(bottomTextItem)
            gridItem.appendChild(document.createElement("div"))
            gridItem.appendChild(loanItem)

            

            itemContainer.appendChild(gridItem)




















            //Top container for item














            
            //#region ---------TEXT ELEMENTS----------------
            var textDiv = document.createElement("div");
            textDiv.classList.add("media-text-container")

            fetch("./php/get-copies-of-media.php?id="+media.id+"&availableOnly=true")
            .then(response => response.json())
            .then(data => {
                var cellCopiesAvailable = document.createElement("div");
                cellCopiesAvailable.textContent = "";
                cellCopiesAvailable.textContent = "Tillgängliga exemplar: " + data.copies.length;
                textDiv.appendChild(cellCopiesAvailable);
            }).catch(error => console.error("Error:", error));

            
            //#region--------------BOTTOM TEXT ELEMENTS-------------



            


            availableMediaTableBody.appendChild(itemContainer);
        });
    }).catch(error => {
        console.error('Error fetching media data:', error);
    });
};








document.addEventListener("DOMContentLoaded", function() {
    gridButton = document.getElementById("grid-button");
    listButton = document.getElementById("list-button");

    mediaContainer = document.getElementById("media-container");
    mediaCatalog = document.getElementById("media-catalog");
    mediaContainer.classList.add("grid-view")

    listButton.addEventListener("click", function() {
        
        mediaContainer.classList.remove("grid-view")
        mediaContainer.classList.add("list-view")
        mediaCatalog.classList.remove("media-grid");
        mediaCatalog.classList.add("media-list");
    });
    gridButton.addEventListener("click", function() {
        mediaContainer.classList.remove("list-view")
        mediaContainer.classList.add("grid-view")
        mediaCatalog.classList.remove("media-list");
        mediaCatalog.classList.add("media-grid");
    });
});






document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("search-input");
    const mediaTypeSelect = document.getElementById("media-type");
    const searchForSelect = document.getElementById("search-for");

    // Trigger new load when user types or changes filter options
    searchInput.addEventListener("input", applyFilters);
    mediaTypeSelect.addEventListener("change", applyFilters);
    searchForSelect.addEventListener("change", applyFilters);

    // Initial load
    loadAllMedia();

    async function applyFilters() {
        const mediaType = mediaTypeSelect.value.trim().toLowerCase();
        const searchFor = searchForSelect.value.trim().toLowerCase();
        const searchTerm = searchInput.value.trim().toLowerCase();

        // Clear previous results
        const container = document.getElementById("media-container");
        container.innerHTML = "";

        // Reload filtered media from backend
        await loadAllMedia(mediaType, searchFor, searchTerm);
    }
});
