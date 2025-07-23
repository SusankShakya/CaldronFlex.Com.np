<nav class="mobile-bottom-menu navbar-expand-lg b-t bg-white fixed-bottom d-block d-sm-none shadow-none z-100" id="ticket-bottom-menu">
    <div class="d-flex justify-content-between pl15 pr15 scroll-to-section">
        <a class="menu-item" aria-current="page" href="#ticket-comments-list" data-target="ticket-comments-list">
            <i data-feather="align-center" class="icon"></i>
        </a>
        <a class="menu-item" aria-current="page" href="#ticket-details-ticket-info" data-target="ticket-details-ticket-info">
            <i data-feather="info" class="icon"></i>
        </a>
        <?php if ($login_user->user_type === "staff") { ?>
            <a class="menu-item" aria-current="page" href="#ticket-tasks-section" data-target="ticket-tasks-section">
                <i data-feather="check-circle" class="icon"></i>
            </a>
        <?php } ?>
    </div>
</nav>