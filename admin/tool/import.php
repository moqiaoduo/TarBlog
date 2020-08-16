<table class="table table-hover" style="width: auto;"">
<?php $count = 0;
foreach ($tools as $tool):
    if ($tool['type'] !== 'import') continue; ?>
    <tr class="tool-item">
        <td class="tool-name"><?php echo $tool['name'] ?><br><a href="?p=<?php echo $tool['p'] ?>">进入</a></td>
        <td class="tool-desc"><?php echo isset($tool['description']) ? $tool['description'] : '暂无说明' ?></td>
    </tr>
    <?php $count++;endforeach; ?>
</table>
<?php if ($count == 0): ?>
    <p>暂无导入工具</p>
<?php endif ?>