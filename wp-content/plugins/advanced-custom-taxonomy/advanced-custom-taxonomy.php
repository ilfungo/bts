<?php
/*
Plugin Name: Erbaio della Gorra Custom Taxonomy
Description: Custom Taxonomy per Erbaio della Gorra  
Author: Federico Porta
Author URI: http://www.federicoporta.com
*/

add_action( 'init', 'register_taxonomy_colori_fiore' );

function register_taxonomy_colori_fiore() {
    $labels = array(
    'name' => _x( 'Colori fiore', 'colori_fiore' ),
    'singular_name' => _x( 'Colore fiore', 'colori_fiore' ),
    'search_items' => _x( 'Cerca colori fiore', 'colori_fiore' ),
    'popular_items' => _x( 'Colori fiore popolari', 'colori_fiore' ),
    'all_items' => _x( 'Tutti i colori fiore', 'colori_fiore' ),
    'parent_item' => _x( 'Parent colore fiore', 'colori_fiore' ),
    'parent_item_colon' => _x( 'Parent colore fiore:', 'colori_fiore' ),
    'edit_item' => _x( 'Modifica colore fiore', 'colori_fiore' ),
    'update_item' => _x( 'Aggiorna colore fiore', 'colori_fiore' ),
    'add_new_item' => _x( 'Aggiungi un nuovo colore fiore', 'colori_fiore' ),
    'new_item_name' => _x( 'Nuovo colore fiore', 'colori_fiore' ),
    'separate_items_with_commas' => _x( 'Separare i colori fiore con la virgola', 'colori_fiore' ),
    'add_or_remove_items' => _x( 'Aggiungi o rinuovi colori fiore', 'colori_fiore' ),
    'choose_from_most_used' => _x( 'Scegli tra i colori fiore già creati ', 'colori_fiore' ),
    'menu_name' => _x( 'Colori fiore', 'colori_fiore' ),
    );
    $args = array(
    'labels' => $labels,
    'public' => true,
    'show_in_nav_menus' => true,
    'show_ui' => true,
    'show_tagcloud' => true,
    'show_admin_column' => true,
    'hierarchical' => false,
    'rewrite' => true,
    'query_var' => true
    );
    register_taxonomy( 'colori_fiore', array('product'), $args );
} 



add_action( 'init', 'register_taxonomy_colori_foglia' );

function register_taxonomy_colori_foglia() {
    $labels = array(
    'name' => _x( 'Colori foglia', 'colori_foglia' ),
    'singular_name' => _x( 'Colore foglia', 'colori_foglia' ),
    'search_items' => _x( 'Cerca colori foglia', 'colori_foglia' ),
    'popular_items' => _x( 'Colori foglia popolari', 'colori_foglia' ),
    'all_items' => _x( 'Tutti i colori foglia', 'colori_foglia' ),
    'parent_item' => _x( 'Parent colore foglia', 'colori_foglia' ),
    'parent_item_colon' => _x( 'Parent colore foglia:', 'colori_foglia' ),
    'edit_item' => _x( 'Modifica colore foglia', 'colori_foglia' ),
    'update_item' => _x( 'Aggiorna colore foglia', 'colori_foglia' ),
    'add_new_item' => _x( 'Aggiungi un nuovo colore foglia', 'colori_foglia' ),
    'new_item_name' => _x( 'Nuovo colore foglia', 'colori_foglia' ),
    'separate_items_with_commas' => _x( 'Separare i colori foglia con la virgola', 'colori_foglia' ),
    'add_or_remove_items' => _x( 'Aggiungi o rinuovi colori foglia', 'colori_foglia' ),
    'choose_from_most_used' => _x( 'Scegli tra i colori foglia già creati ', 'colori_foglia' ),
    'menu_name' => _x( 'Colori foglia', 'colori_foglia' ),
    );
    $args = array(
    'labels' => $labels,
    'public' => true,
    'show_in_nav_menus' => true,
    'show_ui' => true,
    'show_tagcloud' => true,
    'show_admin_column' => true,
    'hierarchical' => false,
    'rewrite' => true,
    'query_var' => true
    );
    register_taxonomy( 'colori_foglia', array('product'), $args );
}                     

//add_action( 'init', 'register_taxonomy_mesi' );

function register_taxonomy_mesi() {
    $labels = array(
    'name' => _x( 'Mesi', 'mesi' ),
    'singular_name' => _x( 'Mese', 'mesi' ),
    'search_items' => _x( 'Cerca mesi', 'mesi' ),
    'all_items' => _x( 'Tutti i mesi', 'mesi' ),
    'parent_item' => _x( 'Parent mese', 'mesi' ),
    'parent_item_colon' => _x( 'Parent mese:', 'mesi' ),
    'edit_item' => _x( 'Modifica mese', 'mesi' ),
    'update_item' => _x( 'Aggiorna mese', 'mesi' ),
    'add_new_item' => _x( 'Aggiungi un nuovo mese', 'mesi' ),
    'new_item_name' => _x( 'Nuovo Mese', 'mesi' ),
    'separate_items_with_commas' => _x( 'Separare i Mesi con la virgola', 'mesi' ),
    'add_or_remove_items' => _x( 'Aggiungi o rinuovi Mesi', 'mesi' ),
    'choose_from_most_used' => _x( 'Scegli tra i Mesi già creati ', 'mesi' ),
    'menu_name' => _x( 'Mesi', 'Mesi' ),
    );
    $args = array(
    'labels' => $labels,
    'public' => true,
    'show_in_nav_menus' => true,
    'show_ui' => true,
    'show_tagcloud' => true,
    'show_admin_column' => true,
    'hierarchical' => false,
    'rewrite' => true,
    'query_var' => true
    );
    register_taxonomy( 'mesi', array('product'), $args );
} 