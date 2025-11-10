<?php
header('Content-Type: application/json');

class ApiHandler{
    private $conn;
    private $dbServer = "localhost";
    private $dbUser = "root";
    private $dbPass = "";
    private $dbName = "library";

    function __construct()
    {
        $this->conn = new mysqli($this->dbServer, $this->dbUser, $this->dbPass, $this->dbName);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    function __destruct()
    {
        $this->conn->close();
    }

    function getSABCategories(){

        $SABQuery = "SELECT * FROM `sab_categories`";
        $stmt = $this->conn->prepare($SABQuery);
        $stmt->execute();

        $result = $stmt->get_result();
        
        $categories = array();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $stmt->close();

        return json_encode($categories);
    }

    function addMedia(string $title, string $author = "", string $SABSignum, int $price = 0, $mediatype, string $ISBN, int $quantity = 1, string $IMDB = ""){

        // if ($book + $audioBook + $film < 1) {
        //     return json_encode(["error" => "At least one media type must be selected."]);
        // }

        if($mediatype == "book" || $mediatype == "audiobook"){
            if(empty($author)){
                return json_encode(["error" => "A media must have an author."]);
            }
            if (strlen($ISBN) != 13) {
                return json_encode(["error" => "ISBN must be 13 characters long."]);
            }
        }
        elseif ($mediatype == "film"){
            if(empty($author)){
                return json_encode(["error" => "A media must have an director."]);
            }
            if (strlen($IMDB) >= 7) {
                return json_encode(["error" => "IMDB ID must be at least 7 characters long."]);
            }
        }
        if($mediatype == "book"){
            $mediatype = "bok";
        }
        if($mediatype == "audiobook"){
            $mediatype = "ljudbok";
        }
        if($mediatype == "film"){
            $mediatype = "film";
        }

        if ($price < 0) {
            return json_encode(["error" => "Price cannot be negative."]);
        }

        if (empty($title)) {
            return json_encode(["error" => "Title cannot be empty."]);
        }

        if ($quantity < 1) {
            return json_encode(["error" => "Quantity must be at least 1."]);
        }

        if(empty($price)){
            return json_encode(["error" => "A media must have a price."]);
        }
        

        $this->conn->begin_transaction();
        try {
            $addMediaQuery = "INSERT INTO media (title, author, SAB_signum, price, ISBN, IMDB, mediatype) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($addMediaQuery);
            $stmt->bind_param("sssisss", $title, $author, $SABSignum, $price, $ISBN, $IMDB, $mediatype);
            if (!$stmt->execute()) {
                throw new Exception("Error: " . $stmt->error);
            }
            if($mediatype == "film"){
                $getMediaId = "SELECT id FROM media WHERE IMDB = ? AND title = ? AND author = ? AND SAB_signum = ? AND price = ?";
                $stmt = $this->conn->prepare($getMediaId);
                $stmt->bind_param("ssssi", $IMDB, $title, $author, $SABSignum, $price);
            } else{
                $getMediaId = "SELECT id FROM media WHERE ISBN = ? AND title = ? AND author = ? AND SAB_signum = ? AND price = ?";
                $stmt = $this->conn->prepare($getMediaId);
                $stmt->bind_param("ssssi", $ISBN, $title, $author, $SABSignum, $price);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                $id = $row['id'];
            }
            for ($i = 0; $i < $quantity; $i++) {
                $addCopyQuery = "INSERT INTO copy (media_id) VALUES(?)";
                $stmt = $this->conn->prepare($addCopyQuery);
                $stmt->bind_param("i", $row['id']);
                if (!$stmt->execute()) {
                    throw new Exception("Error: " . $stmt->error);
                }
            }

            $this->conn->commit();
            $stmt->close();
            return json_encode(["success" => "New media added successfully."]);
        } catch (Exception $e) {
            $this->conn->rollback();
            return json_encode(["error" => $e->getMessage()]);
        }
    }
    //Add copy based on existing media
    function addCopy(int $mediaId, int $quantity = 1) {
        if(empty($quantity)){
            return json_encode(["error" => "choose a number of copies to add"]);
        }

        $this->conn->begin_transaction();
        $addCopyQuery = "INSERT INTO copy (media_id) VALUES(?)";
        $stmt = $this->conn->prepare($addCopyQuery);
        $stmt->bind_param("i", $mediaId);
        for ($i = 0; $i < $quantity; $i++) {
            $stmt->execute();
        }
        $this->conn->commit();
        $stmt->close();
        return json_encode("New copy added successfully.");
    }


    //availableOnly a tri-state: true (only available), false (only unavailable), null (all)
    function getMedia(array $filters = [], $onlyAvailable = false) {    
        $params = [];
        $types = "";

        // Base query
        $query = "SELECT media.*";
        // If showing unavailable (checked out) items, include user info
        if ($onlyAvailable === false) {
            $query .= ", checked_out.*, users.id AS user_id, users.username 
                        "
            ;
        }

        $query .= " FROM media";

        // Join if needed
        if ($onlyAvailable === false) {
            $query = "
                SELECT 
                    media.id AS media_id,
                    media.title,
                    media.author,
                    media.ISBN,
                    media.SAB_signum,
                    media.mediatype,
                    media.price,
                    copy.id AS copy_id,
                    checked_out.id AS checkout_id,
                    checked_out.checkout_date,
                    checked_out.return_date,
                    users.id AS user_id,
                    users.username
                FROM media
                INNER JOIN copy ON copy.media_id = media.id
                INNER JOIN checked_out ON checked_out.c_id = copy.id
                INNER JOIN users ON checked_out.user_id = users.id
                WHERE 1=1
            ";
        }
        else if ($onlyAvailable === true) {
            $query = " 
                    SELECT media.*
                    FROM media
                    WHERE NOT EXISTS (
                    SELECT 1 
                    FROM checked_out 
                    WHERE checked_out.c_id = media.id)";
        }else {
            // All media (available + checked out)
            $query = "
                SELECT media.*,
                       CASE 
                           WHEN checked_out.c_id IS NULL THEN 'available' 
                           ELSE 'checked_out' 
                       END AS status
                FROM media
                LEFT JOIN checked_out ON checked_out.c_id = media.id";
        }

        //$query .= " WHERE 1=1"; // base condition

        // Apply filters
        // if (!empty($filters['filter'])) {
        //     if ($filters['filter'] === "book") {
        //         $query .= " AND book = 1";
        //     } elseif ($filters['filter'] === "audiobook") {
        //         $query .= " AND audiobook = 1";
        //     } elseif ($filters['filter'] === "film") {
        //         $query .= " AND film = 1";
        //     }
        // }
        if (!empty($filters['filter'])) {
            $query .= " AND mediatype = ?";
            $params[] = $filters['filter'];
            $types .= "s";
        }
        $searchFor = false;
        // checks if the user search for something specific
        if (!empty($filters['searchFor']) && $filters['searchTerm'] !== "") {
            switch ($filters['searchFor']) {
                case 'title':
                    $query .= " AND media.title LIKE ?";
                    $params[] = "%" . $filters['searchTerm'] . "%";
                    $types .= "s";
                    $searchFor = true;
                    break;
        
                case 'author':
                    $query .= " AND media.author LIKE ?";
                    $params[] = "%" . $filters['searchTerm'] . "%";
                    $types .= "s";
                    $searchFor = true;
                    break;
        
                case 'category':
                    $query .= " AND media.SAB_signum IN (
                        SELECT signum FROM sab_categories WHERE category LIKE ?
                    )";
                    $params[] = "%" . $filters['searchTerm'] . "%";
                    $types .= "s";
                    $searchFor = true;
                    break;
            }
        }
        // if the user doesnt search for something specific, get the values for everything
        if(!$searchFor){
            $query .= " AND (media.title LIKE ?";
            $params[] = "%" . $filters['searchTerm'] . "%";
            $types .= "s";

            $query .= " OR media.author LIKE ?";
            $params[] = "%" . $filters['searchTerm'] . "%";
            $types .= "s";

            $query .= " OR media.SAB_signum IN (
                SELECT signum FROM sab_categories WHERE category LIKE ?
            ))";
            $params[] = "%" . $filters['searchTerm'] . "%";
            $types .= "s";
        }

        if (!empty($filters['id'])) {
            $query .= " AND media.id = ?";
            $params[] = $filters['id'];
            $types .= "i";
        }

        if (!empty($filters['ISBN'])) {
            $query .= " AND ISBN = ?";
            $params[] = $filters['ISBN'];
            $types .= "s";
        }
        
        if (!empty($filters['title'])) {
            $query .= " AND title LIKE ?";
            $params[] = "%" . $filters['title'] . "%";
            $types .= "s";
        }

        if (!empty($filters['SABSignum'])) {
            $query .= " AND SAB_signum = ?";
            $params[] = $filters['SABSignum'];
            $types .= "s";
        }

        if (!empty($filters['SABCategory'])) {
            $query .= " AND SAB_signum IN (SELECT signum FROM sab_categories WHERE category = ?)";
            $params[] = $filters['SABCategory'];
            $types .= "s";
        }

        // Prepare and execute query
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $result = $stmt->get_result();
        $media = [];
        while ($row = $result->fetch_assoc()) {
            $media[] = $row;
        }
        $stmt->close();
        return json_encode($media);
    }

    function getCopiesOfMedia(array $filters = [], $onlyAvailable = false){
        $params = [];
        $types = "";
        $query = "SELECT copy.id FROM copy";
    
        if(!empty($filters['id'])){
            $query .= " WHERE media_id = ? AND NOT EXISTS (
                SELECT 1 
                FROM checked_out 
                WHERE checked_out.c_id = copy.id
                    AND checked_out.return_date >= NOW()
            )";
            $params[] = $filters['id'];
            $types .= "i";
        }
    
        $stmt = $this->conn->prepare($query);
        if(!empty($params)){
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $copies = [];
        while($row = $result->fetch_assoc()){
            $copies[] = $row;
        }
        $stmt->close();
    
        return json_encode([
            'count' => count($copies),
            'copies' => $copies
        ]);
    }

    function editMedia($params){
        // Expected params: ['title', 'author', 'SABSignum', 'price', 'book', 'audioBook', 'film', 'ISBN', 'IMDB', 'id']
        // input could include any combination of these fields to update

        $updateMediaQuery = "UPDATE media SET ";
        $types = "";
        $paramsToBind = [];

        if(!empty($params['title'])){
            $updateMediaQuery .= "title = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['title'];
        }

        if(!empty($params['author'])){
            $updateMediaQuery .= "author = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['author'];
        }

        if(!empty($params['SABSignum'])){
            $updateMediaQuery .= "SAB_signum = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['SABSignum'];
        }

        if(isset($params['price'])){
            $updateMediaQuery .= "price = ?, ";
            $types .= "i";
            $paramsToBind[] = $params['price'];
        }

        // get the value of a select statement
        if(isset($params['book'])){
            $updateMediaQuery .= "book = ?, ";
            $types .= "s";
            $paramsToBind[] = "book";
        }

        if(isset($params['audioBook'])){
            $updateMediaQuery .= "audiobook = ?, ";
            $types .= "s";
            $paramsToBind[] = "audioBook";
        }

        if(isset($params['film'])){
            $updateMediaQuery .= "film = ?, ";
            $types .= "s";
            $paramsToBind[] = "film";
        }

        if(!empty($params['ISBN'])){
            $updateMediaQuery .= "ISBN = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['ISBN'];
        }

        if(!empty($params['IMDB'])){
            $updateMediaQuery .= "IMDB = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['IMDB'];
        }

        // Remove trailing comma and space
        $updateMediaQuery = rtrim($updateMediaQuery, ", ");
        $updateMediaQuery .= " WHERE id = ?";
        $types .= "i";
        $stmt = $this->conn->prepare($updateMediaQuery);
        $paramsToBind[] = $params['id'];
        $stmt->bind_param($types, ...$paramsToBind);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return json_encode("Media updated successfully.");
            } else {
                $stmt->close();
                return json_encode(["error" => "No changes made or media not found."]);
            }
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }

    }

    function removeCopy(int $mediaId){
        $removeCopyQuery = "DELETE FROM media WHERE id = ?";
        $stmt = $this->conn->prepare($removeCopyQuery);
        $stmt->bind_param("i", $mediaId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return json_encode("Media copy deleted successfully.");
            } else {
                $stmt->close();
                return json_encode(["error" => "Media copy not found."]);
            }
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }
    }

    function removeSingularCopy(int $copyId){
        $removeCopyQuery = "DELETE FROM copy WHERE id = ?";
        $stmt = $this->conn->prepare($removeCopyQuery);
        $stmt->bind_param("i", $copyId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return json_encode("Copy deleted successfully.");
            } else {
                $stmt->close();
                return json_encode(["error" => "Copy not found."]);
            }
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }
    }

    function removeAllCopies($ISBN = "", $IMDB = ""){
        $removeAllCopiesQuery = "DELETE FROM media WHERE ISBN = ? OR IMDB = ?";

    }

    function addUser(string $username, string $password, string $mail, int $is_admin = 0){
        $addUserQuery = "INSERT INTO users (username, password, is_admin, mail) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($addUserQuery);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bind_param("ssis", $username, $hashedPassword, $is_admin, $mail);
        if ($stmt->execute()) {
            $stmt->close();
            return json_encode("New user added successfully.");
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }

    }

    function removeUser(int $userId){
        $removeUserQuery = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($removeUserQuery);
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return json_encode("User deleted successfully.");
            } else {
                $stmt->close();
                return json_encode(["error" => "User not found."]);
            }
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }

    }

    function editUser($params){
        // Expected params: ['username', 'password', 'isAdmin', 'id']
        // input could include any combination of these fields to update

        $updateUserQuery = "UPDATE users SET ";
        $types = "";
        $paramsToBind = [];

        if(!empty($params['username'])){
            $updateUserQuery .= "username = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['username'];
        }

        if(!empty($params['password'])){
            $updateUserQuery .= "password = ?, ";
            $types .= "s";
            $paramsToBind[] = password_hash($params['password'], PASSWORD_DEFAULT);
        }

        if(!empty($params['mail'])){
            $updateUserQuery .= "mail = ?, ";
            $types .= "s";
            $paramsToBind[] = $params['mail'];
        }

        if(isset($params['isAdmin'])){
            $updateUserQuery .= "is_admin = ?, ";
            $types .= "i";
            $paramsToBind[] = $params['isAdmin'];
        }

        // Remove trailing comma and space
        $updateUserQuery = rtrim($updateUserQuery, ", ");
        $updateUserQuery .= " WHERE id = ?";
        $types .= "i";
        $stmt = $this->conn->prepare($updateUserQuery);
        
        $paramsToBind[] = $params['id'];
        $stmt->bind_param($types, ...$paramsToBind);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return json_encode("User updated successfully.");
            } else {
                $stmt->close();
                return json_encode(["error" => "No changes made or user not found."]);
            }
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }
        
    }

    function getUsers(int $userId = 0){
        if($userId !== 0){
            $getUserQuery = "SELECT id, username, is_admin, mail FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($getUserQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();
            
            $user = $result->fetch_assoc();
            $stmt->close();

            return json_encode($user);
        }
        else{
            $getUserQuery = "SELECT id, username, is_admin, mail FROM users";
            $stmt = $this->conn->prepare($getUserQuery);
            $stmt->execute();
    
            $result = $stmt->get_result();
            
            $users = array();
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $stmt->close();
    
            return json_encode($users);
        }
            
    }

    function changePassword($username, $newPassword){
        

        $changePasswordQuery = "UPDATE users WHERE username = ? SET password = ?";
        $stmt = $this->conn->prepare($changePasswordQuery);
        $stmt->bind_param("ss", $username, $newPassword); 
        $stmt->execute();
    }

    function getUserLoanedMedia($filters, int $userId){
        $params = [];
        $types = "i";
        $params[] = $userId;
        $getLoanedMediaQuery = "
            SELECT media.*, copy.id AS copy_id, checked_out.checkout_date, checked_out.return_date, checked_out.c_id
            FROM checked_out
            INNER JOIN copy ON checked_out.c_id = copy.id
            INNER JOIN media ON copy.media_id = media.id
            WHERE checked_out.user_id = ?
        ";

        if (!empty($filters['filter'])) {
            $getLoanedMediaQuery .= " AND mediatype = ?";
            $params[] = $filters['filter'];
            $types .= "s";
        }
        $searchFor = false;
        // checks if the user search for something specific
        if (!empty($filters['searchFor']) && $filters['searchTerm'] !== "") {
            switch ($filters['searchFor']) {
                case 'title':
                    $getLoanedMediaQuery .= " AND media.title LIKE ?";
                    $params[] = "%" . $filters['searchTerm'] . "%";
                    $types .= "s";
                    $searchFor = true;
                    break;
        
                case 'author':
                    $getLoanedMediaQuery .= " AND media.author LIKE ?";
                    $params[] = "%" . $filters['searchTerm'] . "%";
                    $types .= "s";
                    $searchFor = true;
                    break;
        
                case 'category':
                    $getLoanedMediaQuery .= " AND media.SAB_signum IN (
                        SELECT signum FROM sab_categories WHERE category LIKE ?
                    )";
                    $params[] = "%" . $filters['searchTerm'] . "%";
                    $types .= "s";
                    $searchFor = true;
                    break;
            }
        }
        // if the user doesnt search for something specific, get the values for everything
        if(!$searchFor){
            $getLoanedMediaQuery .= " AND (media.title LIKE ?";
            $params[] = "%" . $filters['searchTerm'] . "%";
            $types .= "s";

            $getLoanedMediaQuery .= " OR media.author LIKE ?";
            $params[] = "%" . $filters['searchTerm'] . "%";
            $types .= "s";

            $getLoanedMediaQuery .= " OR media.SAB_signum IN (
                SELECT signum FROM sab_categories WHERE category LIKE ?
            ))";
            $params[] = "%" . $filters['searchTerm'] . "%";
            $types .= "s";
        }

        $stmt = $this->conn->prepare($getLoanedMediaQuery);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        
        $loanedMedia = array();
        while ($row = $result->fetch_assoc()) {
            $loanedMedia[] = $row;
        }
        $stmt->close();

        return json_encode($loanedMedia);
    }
    // add check if media is allready checked out
    function checkoutMedia(int $userId, int $mediaId){
        $stmt = $this->conn->prepare("
            SELECT id 
            FROM copy 
            WHERE media_id = ? 
            AND NOT EXISTS (
                SELECT 1 
                FROM checked_out 
                WHERE checked_out.c_id = copy.id
                    AND checked_out.return_date >= NOW()
            )
            LIMIT 1
        ");
        $stmt->bind_param("i", $mediaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            return json_encode([
                "success" => false,
                "message" => "Ingen tillgänglig kopia av detta media."
            ]);
        }
        $checkoutQuery = "INSERT INTO checked_out (user_id, c_id, checkout_date, return_date) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 3 WEEK))";
        $stmt = $this->conn->prepare($checkoutQuery);
        $stmt->bind_param("ii", $userId, $row['id']);
    
        if ($stmt->execute()) {
            $stmt->close();
            return json_encode([
                "success" => true,
                "message" => "Media checked out successfully."
            ]);
        } else {
            $stmt->close();
            return json_encode([
                "success" => false,
                "message" => "Error: " . $stmt->error
            ]);
        }
    }

    function returnMedia($mediaId, $userId, int $copyId){
        // Hämta lånad kopia
        $query = "
            SELECT media.*, copy.id AS copy_id, checked_out.checkout_date, checked_out.return_date
            FROM checked_out
            INNER JOIN copy ON checked_out.c_id = copy.id
            INNER JOIN media ON copy.media_id = media.id
            WHERE checked_out.c_id = ? AND checked_out.user_id = ?
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $copyId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    
        if (!$row) {
            return json_encode(["error" => "No borrowed copy found."]);
        }
    
        $copyId = $row['copy_id'];
        $today = date("Y-m-d");
    
        // Ta bort lånet
        $deleteCheckoutQuery = "DELETE FROM checked_out WHERE c_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($deleteCheckoutQuery);
        $stmt->bind_param("ii", $copyId, $userId);
        $stmt->execute();
        $stmt->close();
    
        if($row['return_date'] >= $today){
            return json_encode([
                'success' => true,
                'returned media_id' => $row['id'],
                'copy_id' => $copyId
            ]);
        } else {
            // Försenad återlämning, lägg till i late_returns
            $addLateQuery = "INSERT INTO late_returns (media_title, fee, user_id, date_of_return) VALUES (?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($addLateQuery);
            $fee = $row["price"] * 1.5;
            $stmt->bind_param("sdi", $row["title"], $fee, $userId);
            $stmt->execute();
            $stmt->close();
    
            return json_encode([
                "success" => false,
                "error" => "Return date has passed. Fee recorded."
            ]);
        }
    }

    function getLateReturns($filters, int $userId){
        $params = [];
        $types = "i";
        $params[] = $userId;

        $getLateReturnsQuery = "SELECT * FROM late_returns WHERE user_id = ?";

        
        $stmt = $this->conn->prepare($getLateReturnsQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        
        $lateReturns = array();
        while ($row = $result->fetch_assoc()) {
            $lateReturns[] = $row;
        }
        $stmt->close();

        return json_encode($lateReturns);
    }

    function generateToken(){
        $token = bin2hex(random_bytes(16));
        $stmt = $this->conn->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $_SESSION['user_id']);
        $stmt->execute();
        return $token;
    }

    function getUsername($mail){
        $getMailQuery = "SELECT username FROM users WHERE mail = ?";
        $stmt = $this->conn->prepare($getMailQuery);
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $row = $result->fetch_assoc();
        return $row ? $row['username'] : null;
    }

    function getUserId($mail){
        $getIDQuery = "SELECT id FROM users WHERE mail = ?";
        $stmt = $this->conn->prepare($getIDQuery);
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $row = $result->fetch_assoc();
        return $row ? $row['id'] : null;
    }
}