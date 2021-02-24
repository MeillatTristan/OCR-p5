<?php

namespace App\Controller;

use App\Service\TwigRender;
use App\Manager\UsersManager;
use App\Manager\PostsManager;
use App\Manager\CommentsManager;

class FrontendController
{
    private $renderer;
    private $usersManager;
    private $postsManager;

    public function __construct()
    {
        $this->renderer = new TwigRender();
        $this->usersManager = new UsersManager();
        $this->postsManager = new PostsManager();
        $this->commentsManager = new CommentsManager();

        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function homepageView()
    {
        $this->renderer->render('homepage');
    }

    public function PostsView()
    {
        $posts = $this->postsManager->getPosts();
        $this->renderer->render('posts', ['posts' => array_reverse($posts)]);
    }

    public function postView($id){
        $comments = $this->commentsManager->getComments();

        if(isset($_SESSION['successMessage'])){
            if($_SESSION['successMessage'] == "y"){
                $successMessage = "Votre commentaire est bien pris en compte, il est soumis à validation !";
                unset($_SESSION['successMessage']);
                $post = $this->postsManager->getPost($id);
                $this->renderer->render('post', ["successMessage" => $successMessage, "class" => "successMessage", 'post' => $post, 'id' => $id, 'comments' => $comments]);
            }
            else if($_SESSION['successMessage'] == "n"){
                $successMessage = 'Une erreur est survenu, veuillez réessayer.';
                unset($_SESSION['successMessage']);
                $post = $this->postsManager->getPost($id);
                $this->renderer->render('post', ["successMessage" => $successMessage, "class" => "errorMessage", 'post' => $post, 'id' => $id, 'comments' => $comments]);
            }
        }
        else{
        $post = $this->postsManager->getPost($id);
        $this->renderer->render('post', ['post' => $post, 'id' => $id, 'comments' => $comments]);
        }
    } 

    public function connexionView(){
        if(isset($_SESSION['user'])){
            header('Location: /portfolio');
            die();
        }
        if(isset($_SESSION['successMessage'])){
            if($_SESSION['successMessage'] == "n"){
                $successMessage = "Un de vos identifiants est incorrect, veuillez réessayer";
                unset($_SESSION['successMessage']);
                $this->renderer->render('connexion', ["successMessage" => $successMessage, "class" => "errorMessage"]);
            }
            else{
                unset($_SESSION['successMessage']);
            }
        }
        else{
            $this->renderer->render('connexion');
        }
    }

    public function connexionRequest(){
        if(isset($_SESSION['user'])){
            header('Location: /portfolio');
            die();
        }
        $mail = $_REQUEST['mail'];
        $passwordToVerify = $_REQUEST['password'];

        $return = $this->usersManager->loginUser($mail, $passwordToVerify);
        if($return[0] == "y"){
            $_SESSION['user'] = $return[1];
            var_dump($_SESSION['user']);
            header('Location: /portfolio');
            return("");
        }
        else{
            $_SESSION['successMessage'] = "n";
        }
        header('Location: /portfolio/connexion');
    }

    public function deconnexionRequest(){
        unset($_SESSION['user']);
        header('Location: /portfolio');
    }

    public function inscriptionView(){
        if(isset($_SESSION['successMessage'])){
            if($_SESSION['successMessage'] == "y"){
                $successMessage = "Votre inscription à bien été prise en compte, bienvenue !";
                unset($_SESSION['successMessage']);
                $this->renderer->render('inscription', ["successMessage" => $successMessage, "class" => "successMessage"]);
            }
            else if(isset($_SESSION['successMessage'])){
                if($_SESSION['successMessage'] == 'e'){
                    $successMessage = "Cette addresse mail est déjà utilisé, désolé.";
                    unset($_SESSION['successMessage']);
                    $this->renderer->render('inscription', ["successMessage" => $successMessage, "class" => "errorMessage"]);
                }
            }
            else if($_SESSION['successMessage'] == "n"){
                $successMessage = 'Une erreur est survenu, veuillez réessayer.';
                unset($_SESSION['successMessage']);
                $this->renderer->render('inscription', ["successMessage" => $successMessage, "class" => "errorMessage"]);
            }
        }
        else{
            $this->renderer->render('inscription');
        }
        
    }

    public function inscriptionRequest(){
        $name = $_POST['nom'];
        $firstname = $_POST['prenom'];
        $mail = $_POST['mail'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $return = $this->usersManager->createUser($name, $firstname, $mail, $password);
        if($return == "y"){
            $_SESSION['successMessage'] = "y";
        }
        else if($return == 'e'){
            $_SESSION['successMessage'] = "e";
        }
        else{
            $_SESSION['successMessage'] = "n";
        }
        header( "Location: /portfolio/inscription" );
    }

    public function sendmail(){
        $nom = $_REQUEST['nom'];
        $prenom = $_REQUEST['prenom'];
        $phone = $_REQUEST['phone'];
        $mail = $_REQUEST['email'];
        $content = "mail :" . $mail . "<br> Phone : ". $phone . "<br> Message : " .$_REQUEST['message'];

        $headers = "From: tristan.meillat28@gmail.com";
        $dest = "tristan.meillat@sfr.fr";
        $sujet = "message de " . $nom . $prenom . $mail;

        if (mail($dest, $sujet, $content, $headers)) {
            echo "Email envoyé avec succès à $dest ...";
        } else {
            echo "Échec de l'envoi de l'email...";
        }
    }


    public function mentionsLegalesView(){
        $this->renderer->render('mentionsLegales');
    }
}