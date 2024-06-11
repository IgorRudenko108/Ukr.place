<nav aria-label="Page navigation"></nav>
    <ul class="pagination justify-content-center">
        <?php if ($pg['firstPage']): ?>
            <li class="page-item"><a class="page-link" href="<?= ($BASE.$pg['route'].$pg['prefix'].$pg['firstPage']) ?>">Перша</a></li>
        <?php endif; ?>
        <?php if ($pg['prevPage']): ?>
            <li class="page-item"><a class="page-link" href="<?= ($BASE.$pg['route'].$pg['prefix'].$pg['prevPage']) ?>"><i class="glyphicon glyphicon-chevron-left"></i> Попередня</a></li>
        <?php endif; ?>
        <?php foreach (($pg['rangePages']?:[]) as $page): ?>
            <li class="page-item <?= ($page == $pg['currentPage'] ? 'active':'') ?>"><a class="page-link" href="<?= ($BASE.$pg['route'].$pg['prefix'].$page) ?>"><?= ($page) ?></a></li>
        <?php endforeach; ?>
        <?php if ($pg['nextPage']): ?>
            <li class="page-item"><a class="page-link" href="<?= ($BASE.$pg['route'].$pg['prefix'].$pg['nextPage']) ?>"><i class="glyphicon glyphicon-chevron-right"></i> Наступна</a></li>
        <?php endif; ?>
        <?php if ($pg['lastPage']): ?>
            <li class="page-item"><a class="page-link" href="<?= ($BASE.$pg['route'].$pg['prefix'].$pg['lastPage']) ?>">Остання [<?= ($pg['lastPage']) ?>]</a></li>
        <?php endif; ?>
    </ul>
</nav>
<center><br>Сторінка <b><?= ($pg['currentPage']) ?></b> з <b><?= ($pg['allPages']) ?></b></center>