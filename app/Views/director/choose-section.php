<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
?>

<h1>Choose Section</h1>
<p>As a Director, you can access both sections. Please choose which section to view:</p>

<form method="POST" action="/choose-section.php">
    <?= CSRF::tokenField() ?>
    <div class="form-group">
        <label for="section_id">Select Section</label>
        <select id="section_id" name="section_id" required>
            <?php foreach ($roles as $role): ?>
                <option value="<?= $role['section_id'] ?>">
                    <?= htmlspecialchars($role['section_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Continue</button>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

