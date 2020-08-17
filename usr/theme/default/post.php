<?php $this->need('header.php')?>
<div class="container">
    <div class="article">
        <h2 class="post-title"><?php $this->title() ?></h2>
        <ul class="post-meta">
            <li>作者：<?php $this->author() ?></li>
            <li>发布于: <time datetime="<?php echo $this->timeRaw()?>" itemprop="datePublished"><?php echo $this->time('created_at',1)?></time></li>
            <li>更新于: <time datetime="<?php echo $this->timeRaw('updated_at')?>" itemprop="dateModified"><?php echo $this->time('updated_at',1)?></time></li>
            <li>分类:
                <?php foreach ($this->categories() as $category):?>
                    <a href="<?php echo $category->link?>"><?php echo $category->name?></a>
                <?php endforeach;?>
            </li>
            <li itemprop="interactionCount">
                <a itemprop="discussionUrl" href="<?php $this->link()?>#comments">
                    <?php $this->commentsNum('评论','%d 条评论')?></a>
            </li>
        </ul>
        <div class="post-content">
            <?php $this->content() ?>
        </div>
        <div class="prev-next-post">
            <?php if($this->hasPrevPost()):?>
                <a class="prev" href="<?php $this->prevUrl()?>">上一篇：<?php $this->prevTitle()?></a>
            <?php endif;?>
            <?php if($this->hasNextPost()):?>
                <a class="next" href="<?php $this->nextUrl()?>">下一篇：<?php $this->nextTitle()?></a>
            <?php endif;?>
        </div>
        <?php $this->need('comments.php')?>
    </div>
</div>
<?php $this->need('footer.php') ?>