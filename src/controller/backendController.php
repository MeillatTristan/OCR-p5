<?php 

namespace App\Controller;

use App\Service\TwigRender;
use App\Model\UsersManager;
use DateTime;
use App\Model\PostsManager;

class backendController{
  private $renderer;
  private $usersManager;
  private $postsManager;

  public function __construct()
  {
    $this->renderer = new TwigRender();
    $this->usersManager = new UsersManager();
    $this->postsManager = new PostsManager();

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['user'])){
      header('Location: /portfolio');
      exit();
    }
    if($_SESSION['user']->admin != 'y'){
      header('Location: /portfolio');
      exit();
    }
  }


  public function adminPostsView(){
    $posts = $this->postsManager->getPosts();
    $this->renderer->render('adminPosts', ['posts' => $posts]);
  }

  public function adminPostFormView(){
    if(isset($_SESSION['successMessage'])){
      if($_SESSION['successMessage'] == "y"){
          $successMessage = "Votre article à bien été posté !";
          unset($_SESSION['successMessage']);
          $this->renderer->render('adminPostForm', ["successMessage" => $successMessage, "class" => "successMessage"]);
      }
      else if($_SESSION['successMessage'] == "n"){
          $successMessage = 'Une erreur est survenu, veuillez réessayer.';
          unset($_SESSION['successMessage']);
          $this->renderer->render('adminPostForm', ["successMessage" => $successMessage, "class" => "errorMessage"]);
      }
    }
    else{
        $this->renderer->render('adminPostForm');
    }
  }

  public function postAddRequest(){
    $title = $_REQUEST['title'];
    $chapo = $_REQUEST['chapo'];
    $thumbmail = $_FILES["fileToUpload"];
    $content = $_REQUEST['content'];
    $author = $_SESSION['user']->firstname . " " . $_SESSION['user']->name;
    $date = new DateTime('NOW');
    $date = $date->format('d/m/Y');
    $return = $this->postsManager->createPost($title, $thumbmail, $chapo, $content, $author, $date);
     
    unset($_FILES["fileToUpload"]);

    if($return == "y"){
      $_SESSION['successMessage'] = "y";
    }
    else{
        $_SESSION['successMessage'] = "n";
    }
    header( "Location: /portfolio/adminPostForm" );
  }

  public function modifPostView($id){
    if(isset($_SESSION['successMessage'])){
      if($_SESSION['successMessage'] == "y"){
          $successMessage = "Votre article à bien été Modifié !";
          unset($_SESSION['successMessage']);
          $post = $this->postsManager->getPost($id);
          $this->renderer->render('adminPostFormModif', ["successMessage" => $successMessage, "class" => "successMessage", 'post' => $post, 'id' => $id]);
      }
      else if($_SESSION['successMessage'] == "n"){
          $successMessage = 'Une erreur est survenu, veuillez réessayer.';
          unset($_SESSION['successMessage']);
          $post = $this->postsManager->getPost($id);
          $this->renderer->render('adminPostFormModif', ["successMessage" => $successMessage, "class" => "errorMessage", 'post' => $post, 'id' => $id]);
      }
    }
    else{
      $post = $this->postsManager->getPost($id);
      $this->renderer->render('adminPostFormModif', ['post' => $post, 'id' => $id]);
    }
  }

  public function modifPostRequest($id){
    $title = $_REQUEST['title'];
    $chapo = $_REQUEST['chapo'];

    if($_FILES['fileToUpload']['name'] != ""){
      $thumbmail = $_FILES["fileToUpload"];
    }
    else{
      $thumbmail = "n";
    }

    $content = $_REQUEST['content'];
    $date = new DateTime('NOW');
    $date = $date->format('d/m/Y');
    $return = $this->postsManager->modifPost($id, $title, $thumbmail, $content, $chapo, $date);
    if($return == "y"){
      $_SESSION['successMessage'] = "y";
    }
    else{
        $_SESSION['successMessage'] = "n";
    }
    header( "Location: /portfolio/adminPostFormModif/$id" );
  }

  public function deletePost($id){
    $return = $this->postsManager->deletePost($id);
    
    if($return == "n"){
      $_SESSION['successMessage'] = "n";
    }
    header( "Location: /portfolio/adminPosts" );
  }

}