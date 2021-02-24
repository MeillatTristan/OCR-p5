<?php

namespace App\Manager;
use App\Database\ConfigDatabase;
use App\Model\PostModel;

class PostsManager
{

  public function __construct()
  {
    $this->databaseConnexion = new ConfigDatabase();
    $this->database = $this->databaseConnexion->getConnexion();
  }

  
  public function createPost($title, $thumbnail, $description, $chapo, $author, $date){
    // upload thumbnail
    $target_dir = "C:/wamp64/www/portfolio/public/assets/images/";
    $thumbnail['name'] = $thumbnail['name'];
    $target_file = $target_dir . basename($thumbnail["name"]);

    if (!move_uploaded_file($thumbnail["tmp_name"], $target_file)) {
      return('n');
    }


    $request = $this->database->prepare("INSERT INTO posts (title, thumbnail, description, chapo, author, lastMaj, dateCreation) VALUES (:title, :thumbnail, :description, :chapo, :author, :lastMaj, :dateCreation)");
    $params = [':title' => $title, ':thumbnail' => $thumbnail['name'], ':description' => $description, ':chapo' => $chapo, ':author' => $author, ':lastMaj' => $date, ':dateCreation' => $date];
    if($request->execute($params)){
      return("y");
    }
    return('n');
  }

  public function getPosts(){
    $request = $this->database->query("SELECT * FROM posts");
    $posts = $request->fetchAll();
    $postsObjects = [];
    foreach ($posts as $post) {
      $postObject = new PostModel($post['id'], $post['title'], $post['chapo'], $post['thumbnail'], $post['description'], $post['author'], $post['lastMaj'], $post['dateCreation']);
      $postsObjects[] = $postObject;
    }
    return $postsObjects;
  }

  public function getPost($id){
    $request = $this->database->query("SELECT * FROM posts WHERE id = $id");
    $post = $request->fetch();
    $postObject = new PostModel($post['id'], $post['title'], $post['chapo'], $post['thumbnail'], $post['description'], $post['author'], $post['lastMaj'], $post['dateCreation']);
    return $postObject;
  }

  public function modifPost($id, $title, $thumbnail, $description, $chapo, $date){
    if($thumbnail != "n"){
      $target_dir = "C:/wamp64/www/portfolio/public/assets/images/";
      $thumbnail['name'] = $title . "-" . $thumbnail['name'];
      $target_file = $target_dir . basename($thumbnail["name"]);

      if (!move_uploaded_file($thumbnail["tmp_name"], $target_file)) {
        return('n');
      }

      $request = $this->database->prepare("UPDATE posts SET title = :title, thumbnail = :thumbnail, description = :description, chapo = :chapo, lastMaj = :lastMaj WHERE id = :id");
      $params = [':id' => $id, ':title' => $title, ':thumbnail' => $thumbnail['name'], ':description' => $description, ':chapo' => $chapo, ':lastMaj' => $date];
      if($request->execute($params)){
        return("y");
      }
      return('n');
    }
    $request = $this->database->prepare("UPDATE posts SET title = :title, description = :description, chapo = :chapo, lastMaj = :lastMaj WHERE id = :id");
    $params = [':id' => $id, ':title' => $title, ':description' => $description, ':chapo' => $chapo, ':lastMaj' => $date];
    if($request->execute($params)){
      return("y");
    }
    return('n');
  }

  public function deletePost($id){
    $request = $this->database->prepare("DELETE FROM posts WHERE id=:id");
    $params = [':id' => $id];
    if($request->execute($params)){
      return('y');
    }
    return('n');
  }

}