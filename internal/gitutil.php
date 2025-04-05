<?php

function getFileLogs($git_dir, $file): array
{
    $spanContext = \Sentry\Tracing\SpanContext::make()
        ->setOp('git.history.file')
        ->setDescription('load: ' . $file);

    return \Sentry\trace(function () use ($git_dir, $file) {
        $git_dir = escapeshellarg($git_dir);
        $file = escapeshellarg($file);

        $cmd = 'git -C ' . $git_dir . ' --no-pager log --format=oneline --abbrev-commit ' . $file;
        $raw_log = shell_exec($cmd);

        if ($raw_log === null || $raw_log === false) {
            die('Gitからの取得に失敗しました。');
        }

        $raw_log = explode("\n", trim($raw_log));

        $logs = [];

        foreach ($raw_log as $line) {
            $split = explode(' ', $line, 2);
            if (count($split) < 2) {
                continue;
            }

            $logs[] = [
                'commit_id' => $split[0],
                'message' => $split[1],
            ];
        }

        return $logs;

    }, $spanContext);
}