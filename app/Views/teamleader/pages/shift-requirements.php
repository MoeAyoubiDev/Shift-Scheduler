<?php
declare(strict_types=1);

$weekDates = [];
$weekStartDate = new DateTimeImmutable($weekStart);
for ($i = 0; $i < 7; $i++) {
    $weekDates[] = $weekStartDate->modify('+' . $i . ' day')->format('Y-m-d');
}
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card">
            <div class="section-title">
                <h3>Shift Requirements</h3>
                <span>Define required coverage per shift</span>
            </div>
            <form method="post" action="/index.php">
                <input type="hidden" name="action" value="save_requirements">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="table-wrapper">
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <?php foreach ($shiftTypes as $shiftType): ?>
                                <th><?= e($shiftType['shift_type_name']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($weekDates as $date): ?>
                            <tr>
                                <td><?= e($date) ?></td>
                                <?php foreach ($shiftTypes as $shiftType): ?>
                                    <?php
                                        $existing = 0;
                                        foreach ($requirements as $requirement) {
                                            if ($requirement['shift_date'] === $date && (int) $requirement['shift_type_id'] === (int) $shiftType['shift_type_id']) {
                                                $existing = (int) $requirement['required_count'];
                                                break;
                                            }
                                        }
                                    ?>
                                    <td>
                                        <input type="number" name="requirements[<?= e((string) $shiftType['shift_type_id']) ?>][<?= e($date) ?>]" min="0" value="<?= e((string) $existing) ?>">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Save Requirements</button>
                </div>
            </form>
        </div>
    </div>
</section>
