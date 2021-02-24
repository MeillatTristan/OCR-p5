<?php

namespace App\Manager;
use App\Database\ConfigDatabase;
use App\Model\commentModel;

class CommentsManager
{

  public function __construct()
  {
    $this->databaseConnexion = new ConfigDatabase();
    $this->database = $this->databaseConnexion->getConnexion();
  }
  
  public function createComment($idPost, $content, $author, $date){

    $request = $this->database->prepare("INSERT INTO comments (author, content, idPost, date) VALUES (:author, :content, :idPost, :date)");
    $params = [':content' => $content, ':idPost' => $idPost,':author' => $author, ':date' => $date];
    if($request->execute($params)){
      return("y");
    }
    return('n');
  }

  public function getComments(){
    $request = $this->database->query("SELECT * FROM comments");
    $comments = $request->fetchAll();
    $commentsObjects = [];
    foreach ($comments as $comment) {
      $commentObject = new CommentModel($comment['id'], $comment['content'], $comment['author'], $comment['date'], $comment['idPost'], $comment['validate']);
      $commentsObjects[] = $commentObject;
    }
    return $commentsObjects;
  }

  public function getComment($id){
    $request = $this->database->query("SELECT * FROM comments WHERE id = $id");
    $comment = $request->fetch();
    $commentObject = new CommentModel($comment['id'], $comment['content'], $comment['author'], $comment['date'], $comment['idPost'], $comment['validate']);
    return $commentObject;
  }

  public function validateComment($id){
    $comment = $this->getComment($id);
    if($comment->validate == "y"){
      $request = $this->database->prepare("UPDATE comments SET validate = :validate WHERE id = :id");
      $params = [':validate' => "n", "id" => $id];
      $request->execute($params);
    }
    else{
      $request = $this->database->prepare("UPDATE comments SET validate = :validate WHERE id = :id");
      $params = [':validate' => "y", "id" => $id];
      $request->execute($params);
    }
  }

public function deleteComment($id){
    $request = $this->database->prepare("DELETE FROM comments WHERE id=:id");
    $params = [':id' => $id];
    if($request->execute($params)){
      return('y');
    }
      return('n');
  }

}