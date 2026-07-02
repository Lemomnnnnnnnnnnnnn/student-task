<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Page parameters
$page_title = "About Student To-Do List";
$page_active = "about";
include "includes/header.php";
?>

<div class="about-container">
    <!-- Hero Banner -->
    <div class="hero-card">
        <h2>Student To-Do List</h2>
        <p>A smart, elegant task scheduler built specifically for student productivity. Stay ahead of your deadlines, manage your homework, and keep track of your exams.</p>
    </div>

    <!-- Core Features Grid -->
    <div class="section-label">Core System Features</div>
    <div class="about-grid">
        <div class="feature-card">
            <span class="feature-icon">⚡</span>
            <div class="feature-title">Intuitive Scheduling</div>
            <div class="feature-desc">Quickly add tasks, set due dates, categorise by topic (Homework, Project, Exam, Assignment) and assign priority levels (High, Medium, Low).</div>
        </div>
        
        <div class="feature-card">
            <span class="feature-icon">🔍</span>
            <div class="feature-title">Smart Search & Filter</div>
            <div class="feature-desc">Find exactly what you need in seconds. Search through titles and descriptions, or filter tasks by category, priority, and completion status.</div>
        </div>
        
        <div class="feature-card">
            <span class="feature-icon">🚨</span>
            <div class="feature-title">Deadline Protection</div>
            <div class="feature-desc">Never miss a submission. Overdue tasks are highlighted in red or yellow depending on severity, listing exactly how many days they are late.</div>
        </div>

        <div class="feature-card">
            <span class="feature-icon">🌗</span>
            <div class="feature-title">Premium Dark Mode</div>
            <div class="feature-desc">Optimised for late-night study sessions. The application features a smooth dark mode theme that preserves your preference across tabs.</div>
        </div>
    </div>



    <!-- Tips for Productivity -->
    <div class="info-section">
        <div class="section-title">💡 Study & Productivity Tips</div>
        <div class="tip-item">
            <div class="tip-num">1</div>
            <div>
                <strong style="color: var(--text);">Break tasks down:</strong> Large assignments can feel overwhelming. Create smaller tasks with separate checklists to track your incremental progress.
            </div>
        </div>
        <div class="tip-item">
            <div class="tip-num">2</div>
            <div>
                <strong style="color: var(--text);">Prioritize high-value items:</strong> Use the <em>High</em> priority level for tasks that have upcoming deadlines or make up a large percentage of your grade.
            </div>
        </div>
        <div class="tip-item">
            <div class="tip-num">3</div>
            <div>
                <strong style="color: var(--text);">Review your stats:</strong> Check your completion rate on the Profile Page. Seeing your completed task count grow is a great psychological boost!
            </div>
        </div>
    </div>
</div>

<?php
include "includes/footer.php";
?>
