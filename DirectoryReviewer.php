<?php
class DirectoryReviewer
{
    private $htmlTree = '';
    private $cliTree = '';
    private $fillerMethod;
    private $separator;

    function __construct()
    {
        $this->fillerMethod = $this->getFillerMethod();
    }

    public function buildTree(string $dirName, string $offset = ''): void
    {
        $catalog = scandir($dirName, SCANDIR_SORT_NONE);
        foreach ($catalog as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dirName . '\\' . $file;
                $method = $this->fillerMethod;
                $this->$method($filePath, $file, $offset);
                if (!is_link($filePath)) {
                    if (is_dir($filePath)) {
                        $this->buildTree($filePath, str_repeat($this->separator, 5) . $offset);
                    }
                }
            }
        }
    }

    public function getTree(): string
    {
        if (php_sapi_name() === 'cli') {
            $tree = $this->cliTree;
        } else {
            $tree = $this->htmlTree;
        }
        return $tree;
    }

    private function fillBrowserTree(string $filePath, string $file, string $offset): void
    {
        if (is_dir($filePath)) {
            if (is_readable($filePath)) {
                $this->htmlTree .= '<span>' . $offset . '\\' . $file . '</span><br>';
            } else {
                $this->directoryTree .= '<span>' . $offset . 'closed directory' . '</span><br>';
            }
        } else {
            if (is_readable($filePath)){
                $this->htmlTree .= '<span>' . $offset . $file . '</span><br>';
            } else {
                $this->directoryTree .= '<span>' . $offset . 'closed file' . '</span><br>';
            }
        }
    }

    private function fillCliTree(string $filePath, string $file, string $offset): void
    {
        if (is_dir($filePath)) {
            if (is_readable($filePath)) {
                $this->cliTree .= $offset . '\\' . $file."\n";
            } else {
                $this->cliTree .= $offset . '\\' . 'closed directory' . "\n";
            }
        } else {
            if (is_readable($filePath)){
                $this->cliTree .= $offset . $file . "\n";
            } else {
                $this->cliTree .= $offset . 'closed file' . "\n";
            }
        }
    }

    private function getFillerMethod(): string
    {
        if (php_sapi_name() === 'cli') {
            $this->separator = ' ';
            $method = 'fillCliTree';
        } else {
            $this->separator = '&nbsp;';
            $method = 'fillBrowserTree';
        }
        return $method;
    }
}

try{
    $reviewer = new DirectoryReviewer();
    $reviewer->buildTree('.');
    echo $reviewer->getTree();
}
catch (Exception $e)
{
    echo $e->getMessage();
}
