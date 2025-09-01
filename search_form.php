<?php
function renderSearchForm($q = '', $office = '', $from = '', $to = '', $offices = []): void {
?>
<form method="get" class="inline">
    <div class="filters">
        <input type="text" name="q" placeholder="Search title, subject, description, remarks"
                value="<?= htmlspecialchars($q) ?>">

        <select name="office">
            <option value="">-- All Offices --</option>
            <?php foreach ($offices as $o): ?>
                <option value="<?= htmlspecialchars($o) ?>" <?= $office === $o ? 'selected' : '' ?>>
                    <?= htmlspecialchars($o) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">

        <button type="submit">Search</button>
        <a href="index.php" class="button">Clear Filters</a>
    </div>

</form>
<?php
}
?>