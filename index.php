<?php
session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Router
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include 'includes/header.php';

// Content
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'dashboard':
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'pages/dashboard.php';
        break;
    case 'create':
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'pages/create.php';
        break;
    case 'links':
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'pages/links.php';
        break;
    case 'earnings':
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'pages/earnings.php';
        break;
    case 'referrals':
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'pages/referrals.php';
        break;
    case 'logout':
        // Destroy the session and redirect to home page
        session_destroy();
        header('Location: index.php');
        exit;
        break;
    case 'redirect':
        include 'pages/redirect.php';
        break;
    default:
        include 'pages/404.php';
}

// Footer
include 'includes/footer.php';
?>