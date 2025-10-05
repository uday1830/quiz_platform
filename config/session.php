<?php
if(session_status()===PHP_SESSION_NONE)
{
    ini_set('session.cookie_httponly',1);
    ini_set('session.use_only_cookies',1);
    ini_set('session.cookie.secure',0);
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role']==='admin';
}

function requireLogin()
{
    if(!isLoggedIn())
    {
        header('Location: /login.php');
        exit();
    }
}

function requireAdmin(){
    if(!isAdmin())
    {
        header('Location: /login.php');
        exit();
    }
}

function logout()
{
    session_destroy();
    header('Location: /login.php');
    exit();
}

