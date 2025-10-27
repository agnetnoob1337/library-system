<?php

class ApiHandler{
    private $conn;
    private $dbServer = "localhost";
    private $dbUser = "root";
    private $dbPass = "";
    private $dbName = "library";

    function __construct()
    {
        $this->conn = new mysqli($this->dbServer, $this->dbUser, $this->dbPass, $this->dbName);
    }

    function addMedia(string $title, string $author = "", string $SABSignum, int $price = 0, bool $book = false, bool $audioBook = false, bool $film = false, string $ISBN){
        $addMediaQuery = "INSERT INTO media (title, Author, SAB_signum, price, book, audiobook, film, ISBN) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($addMediaQuery);
        $stmt->bind_param("sssdiiss", $title, $author, $SABSignum, $price, $book, $audioBook, $film, $ISBN);
        if ($stmt->execute()) {
            return json_encode("New media added successfully.");
        } else {
            return json_encode("Error: " . $stmt->error);
        }
        /*
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        if ($book + $audioBook + $film < 1) {
            die("At least one media type must be selected.");
        }

        if (strlen($ISBN) != 13) {
            die("ISBN must be 13 characters long.");
        }

        if ($price < 0) {
            die("Price cannot be negative.");
        }

        if (empty($title)) {
            die("Title cannot be empty.");
        }

        $addMediaQuery = "INSERT INTO media (Title, Author, SABSignum, Price, Book, AudioBook, Film, ISBN) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($addMediaQuery);
        $stmt->bind_param("sssdiiss", $title, $author, $SABSignum, $price, $book, $audioBook, $film, $ISBN);

        if ($stmt->execute()) {
            return json_encode("New media added successfully.");
        } else {
            return json_encode("Error: " . $stmt->error);
        }*/
    }

    function getMedia($ISBN, $Title, $filter){

    }
}