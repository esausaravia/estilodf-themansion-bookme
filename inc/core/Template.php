<?php
namespace Bookme\Inc\Core;

/**
 * Class Template
 */
class Template
{

    /** @var string */
    private $template;

    /** @var bool */
    private $is_public;

    /**
     * Constructor.
     *
     * @param string $template
     * @param bool $is_public
     */
    public function __construct($template, $is_public = false)
    {
        $this->template = $template;
        $this->is_public = $is_public;
    }

    /**
     * Render a template file.
     *
     * @throws \Exception
     * @param array  $variables
     * @param bool   $echo
     * @return string|void
     */
    public function display( $variables = array(), $echo = true )
    {
        extract($variables);
        ob_start();
        ob_implicit_flush(0);

        $templates = BOOKME_PATH.'templates/admin/';
        if($this->is_public){
            $templates = BOOKME_PATH.'templates/front/';
        }

        try {
            include $templates . $this->template . '.php';
        } catch ( \Exception $e ) {
            ob_end_clean();
            throw $e;
        }

        if ( $echo ) {
            echo ob_get_clean();
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Create new template.
     *
     * @param string $template
     * @param bool $is_public
     * @return static
     */
    public static function create($template, $is_public = false)
    {
        return new static($template, $is_public);
    }
}