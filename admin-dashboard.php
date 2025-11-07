<?php
session_start();

if (!isset($_SESSION['user_id'] ) || !$_SESSION['is_admin']) {
    header('Location: ./index.html');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin page</title>
    <script src="./js/admin-dashboard.js" defer></script>
    <link rel="stylesheet" href="./css/admin-dashboard.css">
</head>
<body data-section="media">
    <!-- sidebar to choose what the admin sees -->
    <menu id="admin-choose-edit-menu">
        <div class="menu-item active" data-target="media">
            <h3>Media</h3>
        </div>
        <div class="menu-item" data-target="users">
            <h3>Användare</h3>
        </div>
        <div class="menu-item" data-target="borrowed">
            <h3>Utlånat</h3>
        </div>
        <p id="edit-time-left"></p>
        <a href="php/logout.php" id="logout-link">Logga ut</a>
    </menu>
    <main>
        <!-- handle media section -->
        <section data-section="media">
            <dialog id="media-edit-dialog">
                <form method="dialog" id="media-edit-form">
                    <label for="titleEditDialog">
                        Titel:
                    </label>
                    <input type="text" id="titleEditDialog" name="titleEditDialog"  />
                    <br>
                    <label for="authorEditDialog">
                        Författare:
                    </label>
                    <input type="text" id="authorEditDialog" name="authorEditDialog"  />
                    <br>
                    <label for="priceEditDialog">
                        Pris:
                    </label>
                    <input type="text" id="priceEditDialog" name="priceEditDialog"  />
                    <br>
                    <label for="isbnEditDialog" id="isbnEditDialogLabel">
                        ISBN:
                    </label>
                    <input type="text" id="isbnEditDialog" name="isbnEditDialog"  />
                    <br>
                    <label for="imdbEditDialog" id="imdbEditDialogLabel">
                        IMDB:
                    </label>
                    <input type="text" id="imdbEditDialog" name="imdbEditDialog"  />
                    <br>
                    <label for="categoryEditDialog">
                        SAB Kategori:
                    </label>
                    <select name="categoryEditDialog" id="categoryEditDialog">

                    </select>

                    <label for="mediaTypeEditDialog">
                        Media typ:
                    </label>
                    <select name="mediaTypeEditDialog" id="mediaTypeEditDialog">
                        <option value="bok">Bok</option>
                        <option value="ljudbok">Ljudbok</option>
                        <option value="film">Film</option>
                    </select>

                    <menu id="enter-admin-password-menu">
                        <button value="submit">Submit</button>
                        <button value="cancel">Cancel</button>
                    </menu>
                </form>
            </dialog>
            <div id="add-media-con">
                <h3>Lägg till media</h3>   
                <input type="text" name="" id="title" placeholder="Titel">
                <input type="text" name="" id="author" placeholder="Författare">
                <input type="text" name="" id="price" placeholder="Pris">
                <input type="text" name="" id="isbn" placeholder="ISBN">
                <input type="text" name="" id="imdb" placeholder="IMDB" style="display: none;">
                <label id="category-label" for="category">Kategori</label>
                <select name="" id="category">
                </select>
                <label id="media-type-label" for="media-type">Mediatyp</label>
                <select name="" id="media-type">
                    <option value="book">Bok</option>
                    <option value="audiobook">Ljudbok</option>
                    <option value="film">Film</option>
                </select>
    
                <input type="number" id="quantity" min="1" value="" placeholder="Antal kopior">
                <button id="add-media">Lägg till media</button>

                <h3>Lägg till kopior</h3>  
                <input type="number" id="quantity-copy" min="1" value="" placeholder="Antal kopior">
                <input type="text" id="media-id" placeholder="Media id">
                <button id="add-copy">Lägg till kopia av media</button>

                <h3>Ta bort kopia</h3>  
                <input type="number" id="copy-id" placeholder="Kopia id">
                <button id="remove-copy">Ta bort kopia av media</button>
            </div>
    
            <div>
                <h3>Inte utlånade</h3>
                <label for="show-books">Visa böcker</label>
                <label class="switch">
                    <input type="checkbox" id="show-books" checked>
                    <span class="slider"></span>
                </label>
                <!-- container for books -->
                <div id="books-container">
                    <table>
                        <div class="unborrowed-media-container">
                            <h3>Böcker</h3>
                            <input type="search" id="search-input-book" placeholder="Sök media...">
                            <div id="search-for-container">
                                <label for="media-type">Sök efter:</label>
                                <select name="media-type" id="search-for-book">
                                    <option value="">Allt</option>
                                    <option value="title">Titel</option>
                                    <option value="category">Kategori</option>
                                    <option value="author">Författare</option>
                                </select>
                            </div>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Titel</th>
                                    <th>Författare</th>
                                    <th>Pris (SEK)</th>
                                    <th>ISBN</th>
                                    <th>SAB Kategori</th>
                                    <th>Media ID</th>
                                    <th>Kopior tillgängliga (ID)</th>
                                </tr>
                            </thead>
                            <tbody id="available-books-table-body">
                            </tbody>
                        </div>
                    </table>
                </div>

                <label for="show-audiobooks">Visa ljudböcker</label>
                <label class="switch">
                    <input type="checkbox" id="show-audiobooks" checked>
                    <span class="slider"></span>
                </label>

                <!-- container for audiobooks -->
                <div id="audiobooks-container">
                    <table>
                        <h3>Ljudböcker</h3>
                        <input type="search" id="search-input-audiobook" placeholder="Sök media...">
                        <div>
                            <label for="media-type">Sök efter:</label>
                            <select name="media-type" id="search-for-audiobook">
                                <option value="">Allt</option>
                                <option value="title">Titel</option>
                                <option value="category">Kategori</option>
                                <option value="author">Författare</option>
                            </select>
                        </div>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Titel</th>
                                <th>Författare</th>
                                <th>Pris (SEK)</th>
                                <th>ISBN</th>
                                <th>SAB Kategori</th>
                                <th>Media ID</th>
                                <th>Kopior tillgängliga (ID)</th>
                            </tr>
                        </thead>
                        <tbody id="available-audiobook-table-body">
                        </tbody>
                    </table>
                </div>

                <label for="show-movies">Visa filmer</label>
                <label class="switch">
                    <input type="checkbox" id="show-movies" checked>
                    <span class="slider"></span>
                </label>
                <div id="movie-container">
                    <table>
                        <h3>Filmer</h3>
                        <input type="search" id="search-input-movie" placeholder="Sök media...">
                        <div>
                            <label for="media-type">Sök efter:</label>
                            <select name="media-type" id="search-for-movie">
                                <option value="">Allt</option>
                                <option value="title">Titel</option>
                                <option value="category">Kategori</option>
                                <option value="author">Regissör</option>
                            </select>
                        </div>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Titel</th>
                                <th>regissör</th>
                                <th>Pris (SEK)</th>
                                <th>IMDB</th>
                                <th>SAB Kategori</th>
                                <th>Media ID</th>
                                <th>Kopior tillgängliga (ID)</th>
                            </tr>
                        </thead>
                        <tbody id="available-film-table-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- handle borrowed media section -->
        <section data-section="borrowed">
            <div>
                <h3>Utlånade</h3>
                <button id="return-media">Ändra status på media till återlämnad</button>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Titel</th>
                            <th>Författare</th>
                            <th>Pris (SEK)</th>
                            <th>ISBN</th>
                            <th>SAB Kategori</th>
                            <th>Media typ</th>
                            <th>Lånad av</th>
                            <th>Utlånad till</th>
                            <th>Kopia ID</th>
                        </tr>
                    </thead>
                        <tbody id="unavailable-media-table-body">
                    </tbody>
                </table>
            </div>
        </section>

        <!-- handle users section -->
        <section data-section="users">
            <dialog id="add-user-dialog">
                <form method="dialog" id="user-add-form">
                    <label for="username">
                        Användarnamn:
                    </label>
                    <input type="text" id="username" name="username" required />
                    <br>
                    <label for="password">
                        Lösenord:
                    </label>
                    <input type="password" id="password" name="password" required />
                    <br>
                    <label for="mail">
                        Mail:
                    </label>
                    <input type="mail" id="mail" name="mail" required />
                    <br>
                    <label for="is-admin">
                        Är admin:
                    </label>
                    <input type="checkbox" id="is-admin" name="isAdmin"/>
                        
                    <menu id="enter-admin-password-menu"> 
                        <button value="submit">Submit</button>
                        <button value="cancel" formnovalidate >Cancel</button>
                    </menu>
                </form>
            </dialog>
            <dialog id="edit-user-dialog">
                <form method="dialog" id="user-edit-form">
                    <label for="username">
                        Användarnamn:
                    </label>
                    <input type="text" class="username" name="username" />
                    <br>
                    <label for="password">
                        Lösenord:
                    </label>
                    <input type="password" class="password" name="password" />
                    <br>
                    <label for="is-admin">
                        Är admin:
                    </label>
                    <input type="checkbox" class="is-admin" name="isAdmin"/>
                        
                    <menu id="enter-admin-password-menu">
                        <button value="submit">Submit</button>
                        <button value="cancel">Cancel</button>
                    </menu>
                </form>
            </dialog>
            <div>
                <h3>Användar konton</h3>
                <button id="add-user">Lägg till användare</button>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Användarnamn</th>
                            <th>Mail</th>
                            <th>Är admin</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                    </tbody>
                </table>
            </div>
        </section>

        <!-- enter admin password dialog box -->
        <dialog id="enter-admin-password-dialog">
            <form method="dialog" id="enter-admin-password-form">

                <label for="password">
                    Administratör lösenord:
                </label>
                <input type="password" class="password" name="password" />
                    
                <menu id="enter-admin-password-menu">
                    <button id="enter-admin-password-submit" value="submit">Submit</button>
                    <button id="enter-admin-password-cancel" value="cancel" formnovalidate>Cancel</button>
                </menu>
            </form>
        </dialog>
    </main>

</body>
</html>