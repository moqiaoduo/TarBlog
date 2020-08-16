<?php $this->need('header.php') ?>
    <div class="container">
        <div class="article">
            <h2 class="post-title"><?php $this->title() ?></h2>
            <ul class="post-meta">
                <li>发布于:
                    <time datetime="<?php echo $this->timeRaw() ?>"
                          itemprop="datePublished"><?php echo $this->time('created_at', 1) ?></time>
                </li>
                <li>更新于:
                    <time datetime="<?php echo $this->timeRaw('updated_at') ?>"
                          itemprop="dateModified"><?php echo $this->time('updated_at', 1) ?></time>
                </li>
                <li itemprop="interactionCount">
                    <a itemprop="discussionUrl" href="<?php $this->link() ?>#comments">
                        <?php $this->commentsNum('评论', '%d 条评论') ?></a>
                </li>
            </ul>
            <div class="post-content">
                <?php $this->content() ?>
            </div>
            <?php $this->need('comments.php') ?>
        </div>
    </div>
<?php $this->need('footer.php') ?>