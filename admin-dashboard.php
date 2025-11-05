<?php
session_start();

if (!isset($_SESSION['user_id'])) {
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
    <a id="logout-button" href="php/logout.php">Logga ut</a>
    <menu>
        <div class="menu-item active" data-target="media">
            <h3>Media</h3>
        </div>
        <div class="menu-item" data-target="users">
            <h3>Users</h3>
        </div>
    </menu>

    <main>
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
                        <option value="book">Bok</option>
                        <option value="audiobook">Ljudbok</option>
                        <option value="film">Film</option>
                    </select>

                    <menu>
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
                <select name="" id="category">
                </select>
                <select name="" id="media-type">
                    <option value="book">Bok</option>
                    <option value="audiobook">Ljudbok</option>
                    <option value="film">Film</option>
                </select>
    
                <input type="number" id="quantity" min="1" value="1" placeholder="Quantity">
                <button id="add-media">Lägg till media</button>

                
                <input type="number" id="quantity-copy" min="1" value="1" placeholder="Quantity">
                <input type="text" id="media-id" placeholder="Media id">
                <button id="add-copy">Lägg till kopia av media</button>
            </div>
    
            <div>
                <h3>Inte utlånade</h3>
                    <button id="remove-copy">Ta bort kopia</button>
                    <button id="edit-copy">Redigera media kopia</button>
                <table>
                    <h3>Böcker</h3>
                    <input type="search" id="search-input-book" placeholder="Sök media...">
                    <div>
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
                </table>

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
                    
                <menu>
                    <button value="submit">Submit</button>
                    <button value="cancel">Cancel</button>
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
                    
                <menu>
                    <button value="submit">Submit</button>
                    <button value="cancel">Cancel</button>
                </menu>
            </form>
        </dialog>
            <div>
                <h3>Användar konton</h3>
                <button id="delete-user">Ta bort användare</button>
                <button id="add-user">Lägg till användare</button>
                <button id="edit-user">Redigera användare</button>
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
    </main>

</body>
</html>