<?php

namespace DSI\TechPub\ACF;

defined('ABSPATH') || exit;

/**
 * Class ACF
 * @package DSI\TechPub
 */

class ACF
{
    /**
     * File upload folder.
     *
     * @var UPLOAD_DIR
     */
    public const UPLOAD_DIR = '/dsi-tech-pub/files';

    /**
     * ACF upload key name.
     *
     * @var UPLOAD_FIELD_KEY
     */
    public const UPLOAD_FIELD_KEY = 'upload_file';

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        add_filter('acf/upload_prefilter/key=field_632cd8972a6f1', array($this, 'setup_acf_file_upload_dir'), 10, 3);
        add_action('init', array($this, 'create_upload_directory'));
        add_action('init', array($this, 'init_acf_custom_fields'));
    }


    /**
     * Change upload dir by calling WP upload_dir hook.
     * This is specifically done when uploading files by ACF field in the back-end.
     *
     * @param array $errors
     * @param string $file
     * @param string $field
     * @return array
     */
    public function setup_acf_file_upload_dir($errors, $file, $field)
    {
        if (!current_user_can('manage_options')) {
            $errors[] = 'Some error occured';
        }

        add_filter('upload_dir', array($this, 'file_upload_directory'));
        return $errors;
    }


    /**
     * Set upload directory paths.
     *
     * @param array $param
     * @return array
     */
    public function file_upload_directory($param)
    {
        error_log(print_r($param, true));
        error_log(print_r($_POST, true));
        error_log(print_r($_FILES, true));

        $upload_dir = self::UPLOAD_DIR;
        $param['path'] = $param['basedir'] . $upload_dir;
        $param['url'] = $param['baseurl'] . $upload_dir;

        return $param;
    }

    /**
     * Create upload dir.
     */
    public function create_upload_directory()
    {
        $this->get_upload_dir();
        $upload_dir = wp_upload_dir();
        $basedir = $upload_dir['basedir'] . self::UPLOAD_DIR;

        if (!file_exists($basedir)) {
            mkdir($basedir, 0755, true);
        }
    }

    /**
     * Get upload dir.
     * 
     * @return array
     */
    public function get_upload_dir()
    {
        $dir = array();
        $upload_dir = wp_upload_dir();

        $dir['basedir'] = $upload_dir['basedir'] . self::UPLOAD_DIR;
        $dir['baseurl'] = $upload_dir['baseurl'] . self::UPLOAD_DIR;

        if (file_exists($dir['basedir'])) {
            return $dir;
        } else {
            throw new \Exception('File upload path does not exist');
        }
    }

    /**
     * Init ACF field group.
     */
    public function init_acf_custom_fields()
    {
        if (function_exists('acf_add_local_field_group')) :
            acf_add_local_field_group(array(
                'key' => 'group_632cd67f69f4e',
                'title' => 'Tech Library Management',
                'fields' => array(
                    array(
                        'key' => 'field_632cd6802a6eb',
                        'label' => 'Part Numbers',
                        'name' => 'part_numbers',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ),
                    array(
                        'key' => 'field_632cd7602a6ec',
                        'label' => 'User Guide Number',
                        'name' => 'user_guide_number',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ),
                    array(
                        'key' => 'field_632cd7722a6ed',
                        'label' => 'Revision Date/Number',
                        'name' => 'revision_number',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ),
                    array(
                        'key' => 'field_632cd79e2a6ee',
                        'label' => 'Supported Products',
                        'name' => 'supported_products',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ),
                    array(
                        'key' => 'field_632cd7c12a6ef',
                        'label' => 'Aircraft Make',
                        'name' => 'aircraft_make',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ),
                    array(
                        'key' => 'field_632cd8282a6f0',
                        'label' => 'Aircraft Model',
                        'name' => 'aircraft_model',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ),
                    array(
                        'key' => 'field_632cd8972a6f1',
                        'label' => 'Upload File',
                        'name' => 'upload_file',
                        'type' => 'file',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'return_format' => 'id',
                        'library' => 'uploadedTo',
                        'min_size' => '',
                        'max_size' => '',
                        'mime_types' => '',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'product',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
                'show_in_rest' => 0,
            ));
        endif;
    }
}
