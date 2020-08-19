<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title><?php $this->options->title(); $this->archiveTitle([
            'category'  =>  '分类 %s 下的文章',
            'search'    =>  '包含关键字 %s 的文章',
            'tag'       =>  '标签 %s 下的文章',
            'author'    =>  '%s 发布的文章'
        ]); ?></title>
    <link rel="stylesheet" href="<?php $this->asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?php $this->asset('css/mobile.css') ?>">
    <link rel="stylesheet" href="<?php $this->asset('css/prism.css') ?>">
    <?php $this->header()?>
</head>
<body>
<header>
    <div class="container">
        <div class="top">
            <div class="logo"><a href="<?php $this->options->siteUrl() ?>"><?php $this->options->title() ?></a></div>
            <div class="site-search">
                <form id="search" method="get" action="<?php echo route('index') ?>">
                    <label for="s" class="sr-only">搜索关键字</label>
                    <input type="text" name="s" class="text" placeholder="输入关键字搜索" value="<?php $this->search()?>">
                    <button type="submit" class="submit">搜索</button>
                </form>
            </div>
        </div>
        <nav class="menu">
            <a href="<?php $this->options->siteUrl() ?>">首页</a>
            <?php $this->page()->to($page);
            while($page->next()):?>
                <a href="<?php $page->link() ?>"><?php $page->title() ?></a>
            <?php endwhile;?>
            <div class="category-dropdown">
                分类
                <div class="dropdown-list">
                    <?php $this->category()->to($category);
                    while($category->next()):?>
                        <a href="<?php $category->link()?>"><?php $category->name()?></a>
                    <?php endwhile;?>
                </div>
            </div>
        </nav>
    </div>
</header>