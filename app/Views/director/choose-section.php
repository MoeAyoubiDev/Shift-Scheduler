<?php
declare(strict_types=1);
?>
<div class="dashboard-container">
    <div class="card">
        <div class="hero-row">
            <div>
                <h2>Choose a Section</h2>
                <p>Select which section you want to review as Director. You can switch sections at any time.</p>
            </div>
        </div>
        
        <div class="sections-grid">
            <?php foreach ($user['sections'] as $section): ?>
                <form method="post" action="/index.php" class="section-card">
                    <input type="hidden" name="action" value="select_section">
                    <input type="hidden" name="section_id" value="<?= e((string) $section['section_id']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="section-card-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    
                    <h3 class="section-card-title"><?= e($section['section_name']) ?></h3>
                    <p class="section-card-description">View employees, schedules, performance, and statistics for this section.</p>
                    
                    <button type="submit" class="btn btn-primary section-card-button">
                        <span>Open Dashboard</span>
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.75 13.5L11.25 9L6.75 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
