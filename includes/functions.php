
<?php

// Escape output before displaying user-supplied text in HTML.
function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Redirect to another page and stop execution.
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

// Create a CSRF token for forms.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Validate a submitted CSRF token.
function verify_csrf_token(): bool
{
    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    return is_string($submitted)
        && is_string($stored)
        && $stored !== ''
        && hash_equals($stored, $submitted);
}

// Store a one-time message for the next page.
function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

// Retrieve and remove a one-time message.
function get_flash(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);

    return $message;
}
