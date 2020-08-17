<?php
/**
 * Created by tarblog.
 * Date: 2020/5/30
 * Time: 0:14
 *
 * @var \Core\Options $options
 */

use Utils\Route;

$options = $app->make('options');

$indexPage = $options->get('indexPage', 0);

if ($indexPage > 0) {
    Route::add('/', 'App\Article\Page')->name('page.index');

    if ($options->showArticleList && $alu = $options->articleListUrl)
        Route::add($alu, 'App\Archive\Index');
} else {
    Route::add('/', 'App\Archive\Index');
}

Route::add($category_url = $options->categoryUrl, 'App\Archive\Category')->name('category');

Route::add($post_url = $options->postUrl, 'App\Article\Post')->name('post')->where(['cid' => '{cid}', 'year' => Route::YEAR_PATTERN,
    'month' => Route::MONTH_DAY_PATTERN, 'day' => Route::MONTH_DAY_PATTERN]);

Route::add($post_url . '/comment', 'App\Comment')->name('post.comment');

Route::add($page_url = $options->pageUrl, 'App\Article\Page')->name('page')->where(['cid' => '{cid}']);

Route::add($page_url . '/comment', 'App\Comment')->name('page.comment');

Route::add('/author/{id}', 'App\Archive\Author')->name('author');
