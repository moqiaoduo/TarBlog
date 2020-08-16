<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 16:16
 */
?>
<ul class="pagination pagination-sm no-margin">
    <li class="page-item<?php if ($curr == 1) echo ' disabled' ?>">
        <?php if ($curr == 1): ?>
            <span class="page-link">«</span>
        <?php else: ?>
            <a class="page-link" href="<?php echo $query . $params['query_name'] . '=' . ($curr - 1) ?>">«</a>
        <?php endif ?>
    </li>
    <?php foreach ($pages as $page): ?>
        <li class="page-item<?php if ($curr == $page) echo ' active'; elseif ($page == '-') echo ' disabled' ?>">
            <?php if ($page == '-'): ?>
                <span class="page-link">...</span>
            <?php elseif ($curr == $page): ?>
                <span class="page-link"><?php echo $page ?></span>
            <?php else: ?>
                <a class="page-link" href="<?php echo $query . $params['query_name'] .'=' . $page ?>">
                    <?php echo $page ?>
                </a>
            <?php endif ?>
        </li>
    <?php endforeach ?>
    <li class="page-item<?php if ($curr == $total_page) echo ' disabled' ?>">
        <?php if ($curr == $total_page): ?>
            <span class="page-link">»</span>
        <?php else: ?>
            <a class="page-link" href="<?php echo $query . $params['query_name'] . '=' . ($curr + 1) ?>">»</a>
        <?php endif ?>
    </li>
</ul>