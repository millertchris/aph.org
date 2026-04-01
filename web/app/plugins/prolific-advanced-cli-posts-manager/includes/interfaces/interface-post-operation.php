<?php

if (!defined('ABSPATH')) {
    exit;
}

interface Prolific_Post_Operation_Interface {
    
    public function validate_operation($args);
    
    public function execute_operation($posts, $args);
    
    public function get_operation_summary($posts, $args);
    
    public function requires_confirmation();
    
    public function supports_dry_run();
    
    public function get_required_capabilities();
    
    public function get_operation_name();
    
    public function get_operation_description();
}