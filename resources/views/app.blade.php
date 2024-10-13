<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <script>
        window.config = @js([
            'comments' => [
                'reactions' => \App\Enums\CommentReaction::getCases(),
                'report_reasons' => \App\Enums\CommentReportReason::getCases(),
                'max_depth' => \App\Models\Comment::MAX_DEPTH,
                'replies_limit' => \App\Models\Comment::REPLIES_LIMIT,
            ],
        ])
    </script>
</head>

<body class="font-sans antialiased bg-main">
    @inertia
</body>

</html>
