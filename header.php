<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>
        <?php wp_title('•', true, 'right'); ?>
    </title>
    <?php $favicon = get_field('favicon', 'option') ?>
    <link rel="shortcut icon" type="image/png" href="<?= $favicon; ?>">
    <link rel="icon" href="<?= $favicon; ?>">
    <link rel="apple-touch-icon" href="<?= $favicon; ?>">
    <link rel="apple-touch-icon-precomposed" href="<?= $favicon; ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $favicon; ?>">
    <meta name="google-site-verification" content="ahIwZnq43QbJ9z88m4P9qInKYCSMsFAVufLKtwtUq0g" />
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php echo esc_url(get_bloginfo('pingback_url')); ?>">

    <meta name="theme-color" content="#FFFFFF">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <?php wp_head(); ?>

    <?php $headerScripts = get_field('header_scripts', 'option'); ?>
    <?php echo $headerScripts; ?>
</head>

<body <?php body_class(); ?>>

    <?php $bodyTopScripts = get_field('body_top_scripts', 'option'); ?>
    <?php echo $bodyTopScripts; ?>

    <!-- Header -->
    <?php include(locate_template('components/header/header-a.php', false, false)); ?>

    <!-- Preloader -->
    <?php include(locate_template('components/preloader/preloader-a.php', false, false)); ?>

    <div class="c--progress-a">
        <span id="progress-counter"></span>
    </div>

    <!-- transitions -->
    <div class="c--transition-a js--transition">
        <div class="c--transition-a__item c--transition-a__item--first"></div>
        <div class="c--transition-a__item c--transition-a__item--second"></div>
    </div>

    <main id="swup">
        <div id="main-content">