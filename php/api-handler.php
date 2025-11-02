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


    function addMedia(string $title, string $author = "", string $SABSignum, int $price = 0, bool $book = false, bool $audioBook = false, bool $film = false, string $ISBN, int $quantity = 1, string $IMDB = ""){

        if ($book + $audioBook + $film < 1) {
            return json_encode(["error" => "At least one media type must be selected."]);
        }

        if($book || $audioBook){
            if (strlen($ISBN) != 13) {
                return json_encode(["error" => "ISBN must be 13 characters long."]);
            }
        }
        elseif ($film){
            if (strlen($IMDB) >= 7) {
                return json_encode(["error" => "IMDB ID must be at least 7 characters long."]);
            }
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

        $this->conn->begin_transaction();
        try {
            $addMediaQuery = "INSERT INTO media (title, author, SAB_signum, price, book, audiobook, film, ISBN, IMDB) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($addMediaQuery);

            for ($i = 0; $i < $quantity; $i++) {
                $stmt->bind_param("sssdiisss", $title, $author, $SABSignum, $price, $book, $audioBook, $film, $ISBN, $IMDB);
                if (!$stmt->execute()) {
                    throw new Exception("Error: " . $stmt->error);
                }
            }

            $this->conn->commit();
            $stmt->close();
            return json_encode("New media added successfully.");
        } catch (Exception $e) {
            $this->conn->rollback();
            return json_encode(["error" => $e->getMessage()]);
        }
    }


    //availableOnly a tri-state: true (only available), false (only unavailable), null (all)
    function getMedia(array $filters = [], $onlyAvailable = false) {
        $params = [];
        $types = "";

        // Base query
        $query = "SELECT media.*";
        // If showing unavailable (checked out) items, include user info
        if ($onlyAvailable === false) {
            $query .= ", checked_out.*, users.id AS user_id, users.username";
        }

        $query .= " FROM media";

        // Join if needed
        if ($onlyAvailable === false) {
            $query .= " INNER JOIN checked_out ON checked_out.m_id = media.id
                        INNER JOIN users ON checked_out.user_id = users.id";
        }

        $query .= " WHERE 1=1"; // base condition

        // Apply filters
        if (!empty($filters['filter'])) {
            if ($filters['filter'] === "book") {
                $query .= " AND book = 1";
            } elseif ($filters['filter'] === "audiobook") {
                $query .= " AND audiobook = 1";
            } elseif ($filters['filter'] === "film") {
                $query .= " AND film = 1";
            }
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

        if(isset($params['book'])){
            $updateMediaQuery .= "book = ?, ";
            $types .= "i";
            $paramsToBind[] = $params['book'];
        }

        if(isset($params['audioBook'])){
            $updateMediaQuery .= "audiobook = ?, ";
            $types .= "i";
            $paramsToBind[] = $params['audioBook'];
        }

        if(isset($params['film'])){
            $updateMediaQuery .= "film = ?, ";
            $types .= "i";
            $paramsToBind[] = $params['film'];
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

    function removeAllCopies($ISBN = "", $IMDB = ""){
        $removeAllCopiesQuery = "DELETE FROM media WHERE ISBN = ? OR IMDB = ?";

    }

    function addUser(string $username, string $password, int $is_admin = 0){
        $addUserQuery = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($addUserQuery);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bind_param("ssi", $username, $hashedPassword, $is_admin);
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
            $getUserQuery = "SELECT id, username, is_admin FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($getUserQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();
            
            $user = $result->fetch_assoc();
            $stmt->close();

            return json_encode($user);
        }
        else{
            $getUserQuery = "SELECT id, username, is_admin FROM users";
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

    function getUserLoanedMedia(int $userId){
        $getLoanedMediaQuery = "SELECT media.*, checked_out.checkout_date, checked_out.return_date 
                                FROM media 
                                INNER JOIN checked_out ON media.id = checked_out.m_id 
                                WHERE checked_out.user_id = ?";
        $stmt = $this->conn->prepare($getLoanedMediaQuery);
        $stmt->bind_param("i", $userId);
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
        $checkoutQuery = "INSERT INTO checked_out (user_id, m_id, checkout_date, return_date) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 3 WEEK))";
        $stmt = $this->conn->prepare($checkoutQuery);
        $stmt->bind_param("ii", $userId, $mediaId);

        if ($stmt->execute()) {
            $stmt->close();
            return json_encode("Media checked out successfully.");
        } else {
            $stmt->close();
            return json_encode(["error" => "Error: " . $stmt->error]);
        }

    }


    function returnMedia($mediaId, $userId){
        $getReturnDateQuery = "SELECT media.*, checked_out.checkout_date, checked_out.return_date 
                                FROM media 
                                INNER JOIN checked_out ON media.id = checked_out.m_id 
                                WHERE media.id = ?";
        $returnDateStmt = $this->conn->prepare($getReturnDateQuery);
        $returnDateStmt->bind_param("i", $mediaId);

        $returnDateStmt->execute();
        
        $result = $returnDateStmt->get_result();
        
        $row = $result->fetch_assoc();
           
        $returnDateStmt->close();

        if($row['return_date'] > date("Y-m-d")){
            $returnQuery = "DELETE FROM checked_out WHERE m_id = ?";
            $stmt = $this->conn->prepare($returnQuery);

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $stmt->close();

            return json_encode([
                'returned' => $mediaId,     
            ]);
        }
        else if($row['return_date'] <= date("Y-m-d")){

            $deleteCheckoutQuery = "DELETE FROM checked_out WHERE m_id = ?";
            $stmt = $this->conn->prepare($deleteCheckoutQuery);

            
            $stmt->bind_param("i", $mediaId);
            $stmt->execute();

            $stmt->close();

            $deleteFromMediaQuery = "DELETE FROM media WHERE id = ?";
            $stmt = $this->conn->prepare($deleteFromMediaQuery);

            $stmt->bind_param("i", $mediaId);
            $stmt->execute();
            
            $stmt->close();

            $addToLateReturnsQuery = "INSERT INTO late_returns (media_title, fee, user_id, date_of_return) VALUES (?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($addToLateReturnsQuery);

            $fee = $row["price"] * 1.5;
            $stmt->bind_param("sdi", $row["title"], $fee, $userId);

            $stmt->execute();
            $stmt->close();
             

            return json_encode(["error" => "Return date has passed. Please contact the library staff for further assistance."]);
        }

    }

    function getLateReturns(int $userId){
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
}