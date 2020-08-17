<?php
/**
 * TarBlog默认主题
 *
 * @package 默认主题
 * @author TarBlog
 * @version 0.1
 * @link https://tarblog.cn
 */
$this->need('header.php') ?>
    <div class="container">
        <article>
            <?php if ($this->have()):
                while ($this->next()):?>
                    <div class="item">
                        <h2 class="post-title"><a href="<?php $this->link() ?>"><?php $this->title() ?></a></h2>
                        <ul class="post-meta">
                            <li>发布于:
                                <time datetime="<?php $this->timeRaw() ?>"
                                      itemprop="datePublished"><?php $this->time() ?></time>
                            </li>
                            <li>更新于:
                                <time datetime="<?php $this->timeRaw('updated_at') ?>"
                                      itemprop="dateModified"><?php $this->time('updated_at') ?></time>
                            </li>
                            <li>分类:
                                <?php foreach ($this->categories() as $category): ?>
                                    <a href="<?php echo $category->link ?>"><?php echo $category->name ?></a>
                                <?php endforeach; ?>
                            </li>
                            <li itemprop="interactionCount">
                                <a itemprop="discussionUrl" href="<?php $this->link() ?>#comments">
                                    <?php $this->commentsNum('评论', '%d 条评论') ?></a>
                            </li>
                        </ul>
                        <div class="post-preview">
                            <p><?php $this->preview() ?></p>
                            <p><a href="<?php $this->link() ?>">阅读全文</a></p>
                        </div>
                    </div>
                <?php endwhile;
            else:
                if ($this->is('search')):?>
                    <p>搜索不到关键词相关的文章</p>
                <?php elseif ($this->is('category')): ?>
                    <p>分类下没有任何文章</p>
                <?php else: ?>
                    <p>目前没有任何文章</p>
                <?php endif;
            endif ?>
            <?php $this->pageNav() ?>
        </article>
        <aside>
            <section class="widget">
                <h3 class="widget-title">功能</h3>
                <ul class="widget-list">
                    <?php if ($this->user->hasLogin()): ?>
                        <li class="last"><a href="<?php $this->options->adminUrl(); ?>">进入后台
                                (<?php $this->user->screenName(); ?>)</a></li>
                        <li><a href="<?php $this->options->logoutUrl(); ?>">退出</a></li>
                    <?php else: ?>
                        <li class="last"><a href="<?php $this->options->loginUrl(); ?>">登录</a></li>
                    <?php endif; ?>
                    <li><a href="https://tarblog.cn" target="_blank">TarBlog</a></li>
                </ul>
            </section>
        </aside>
    </div>
<?php $this->need('footer.php') ?>