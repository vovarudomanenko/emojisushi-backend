<?php if (!$this->fatalError): ?>

    <div class="modal-body">

        <div class="scoreboard">
            <div data-control="toolbar">
                <div class="scoreboard-item title-value">
                    <h4><?= __("Created") ?></h4>
                    <p><?= $importResults->created ?></p>
                </div>
                <div class="scoreboard-item title-value">
                    <h4><?= __("Updated") ?></h4>
                    <p><?= $importResults->updated ?></p>
                </div>
                <?php if ($importResults->skippedCount): ?>
                    <div class="scoreboard-item title-value">
                        <h4><?= __("Skipped") ?></h4>
                        <p><?= $importResults->skippedCount ?></p>
                    </div>
                <?php endif ?>
                <?php if ($importResults->warningCount): ?>
                    <div class="scoreboard-item title-value">
                        <h4><?= __("Warnings") ?></h4>
                        <p><?= $importResults->warningCount ?></p>
                    </div>
                <?php endif ?>
                <div class="scoreboard-item title-value">
                    <h4><?= __("Errors") ?></h4>
                    <p><?= $importResults->errorCount ?></p>
                </div>
            </div>
        </div>

        <?php if ($importResults->hasMessages): ?>
            <?php
                $tabs = [
                    'skipped' => __("Skipped Rows"),
                    'warnings' => __("Warnings"),
                    'errors' => __("Errors"),
                ];

                if (!$importResults->skippedCount) unset($tabs['skipped']);
                if (!$importResults->warningCount) unset($tabs['warnings']);
                if (!$importResults->errorCount) unset($tabs['errors']);
            ?>
            <div class="control-tabs secondary-tabs" data-control="tab">
                <ul class="nav nav-tabs">
                    <?php $count = 0; foreach ($tabs as $code => $tab): ?>
                        <li class="<?= $count++ == 0 ? 'active' : '' ?>">
                            <a href="#importTab<?= $code ?>">
                                <?= $tab ?>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
                <div class="tab-content">
                    <?php $count = 0; foreach ($tabs as $code => $tab): ?>
                        <div class="tab-pane <?= $count++ == 0 ? 'active' : '' ?>">
                            <div class="control-simplelist is-divided is-scrollable size-small" data-control="simplelist">
                                <ul>
                                    <?php foreach ($importResults->{$code} as $row => $message): ?>
                                        <li>
                                            <strong><?= e(trans('backend::lang.import_export.row', ['row' => $row + $sourceIndexOffset])) ?></strong>
                                            - <?= e($message) ?>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>

    </div>
    <div class="modal-footer">
        <a
            href="<?= $returnUrl ?>"
            class="btn btn-success"
            data-dismiss="popup">
            <?= __("Complete") ?>
        </a>
        <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="popup">
            <?= __("Close") ?>
        </button>
    </div>

<?php else: ?>

    <div class="modal-body">
        <p class="flash-message static error"><?= e($this->fatalError) ?></p>
    </div>
    <div class="modal-footer">
        <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="popup">
            <?= __("Close") ?>
        </button>
    </div>

<?php endif ?>
