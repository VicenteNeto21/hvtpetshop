<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation" class="flex justify-center items-center gap-2">
    <?php if ($pager->hasPrevious()) : ?>
        <a href="<?= $pager->getFirst() ?>" aria-label="First" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
            <i data-lucide="chevrons-left" class="w-5 h-5"></i>
        </a>
        <a href="<?= $pager->getPrevious() ?>" aria-label="Previous" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
    <?php endif ?>

    <ul class="flex items-center gap-1 bg-white p-1 rounded-xl shadow-sm border border-slate-100">
        <?php foreach ($pager->links() as $link) : ?>
            <li>
                <a href="<?= $link['uri'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-all <?= $link['active'] ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'text-slate-600 hover:bg-slate-50' ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>

    <?php if ($pager->hasNext()) : ?>
        <a href="<?= $pager->getNext() ?>" aria-label="Next" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
            <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </a>
        <a href="<?= $pager->getLast() ?>" aria-label="Last" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
            <i data-lucide="chevrons-right" class="w-5 h-5"></i>
        </a>
    <?php endif ?>
</nav>
