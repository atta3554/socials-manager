<?php

namespace SocialsManager;

if(!defined('ABSPATH')) exit();

class Loader {
  protected $actions = [];
  protected $filters = [];

  public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
    $this->actions[] = [
      'hook'          => $hook,
      'component'     => $component,
      'callback'      => $callback,
      'priority'      => $priority,
      'accepted_args' => $accepted_args
    ];
  }

  public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
    $this->filters[] = [
      'hook'          => $hook,
      'component'     => $component,
      'callback'      => $callback,
      'priority'      => $priority,
      'accepted_args' => $accepted_args
    ];
  }

  public function run() {
    foreach($this->actions as $action) {
      add_action($action['hook'], [$action['component'], $action['callback']], $action['priority'], $action['accepted_args']);
    }

    foreach($this->filters as $filter) {
      add_filter($filter['hook'], [$filter['component'], $filter['callback']], $filter['priority'], $filter['accepted_args']);
    }
  }
}