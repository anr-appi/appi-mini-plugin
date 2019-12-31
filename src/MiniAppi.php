<?php
class MiniAppiPlugin {

    public function __construct() {
        $this->concepts_table_name = $this->concepts_table_name();
    }

    public function activation() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $query = "CREATE TABLE $this->concepts_table_name (
            concept varchar(100),
            definition TEXT,
            alpic_number varchar(10),
            alpic_title varchar(100),
            alpic_number_2 varchar(10),
            alpic_title_2 varchar(100),
            alw_number varchar(10),
            alw_title varchar(100),
            alf_number varchar(10),
            alf_title varchar(100),
            hidden VARCHAR(3),
            commentaires TEXT,
            alw_number_2 TEXT,
            alw_title_2 TEXT,
            alf_number_2 TEXT,
            alf_title_2 TEXT,
            PRIMARY KEY  (concept)
        ) $charset_collate;";
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($query);
    }

    private function concepts_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'mini_appi_concepts';
    }

    public function deactivation() {
    }

    public function init() {
        add_action('admin_menu', array (
                $this,
                'add_menu'
        ));
        add_action('admin_init', array (
                $this,
                'register_settings'
        ));
        add_shortcode(self::SHORTCODE, array (
                $this,
                'render_shortcode'
        ));
    }

    const SHORTCODE = 'mini_appi';

    public function add_menu() {
        add_menu_page("Mini APPI", "Mini APPI", 'mini_appi', 'mini-appi', array (
                $this,
                'menu_page'
        ));

        function load_scripts() {
            $plugin_url = plugin_dir_url(__FILE__);

            wp_enqueue_style('style', $plugin_url . "/mini-appi.css");
            wp_enqueue_script('script-name', $plugin_url . "/mini-appi.js", '', '', false);
        }

        add_action('admin_print_styles', 'load_scripts');
    }

    const MAX_ROWS_QUERY_PARAM = 'max_rows';
    const REQUEST_QUERY_PARAM = 'requete';
    const POS_QUERY_PARAM = 'pos';

    // début du menu de configuration du plugin mini-appi
    public function menu_page() {
        ?>
<div class="wrap">
    <h1>Mini APPI</h1>

    <script type="text/javascript">
    var b = document.getElementsByTagName("body")[0];
    b.setAttribute("onload", "populate()");
    </script>

    <h2>Voir le contenu de la base de données :</h2>

    <div>

        <form id="select_nbr" method="post" action="" accept-charset="UTF-8" style="display: inline">
            <select class="select_page" name="pos" id="id_of_page" onChange="submit()">
<?php
        // formulaire qui correspond au nombre d'entrées affiché dans la page
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(concept) FROM $this->concepts_table_name");
        if (! isset($_POST [self::MAX_ROWS_QUERY_PARAM])) {
            $nbr_de_page = ($count / 9999);
            $i = 0;
            $p = 1;
            while ( $i < $nbr_de_page ) {
                ?>
                <option value="<?php echo 25*$i ?>"><?php echo $p ?></option>
<?php
                $i ++;
                $p ++;
            }
            echo $nbr_de_page;
        } else {
            $nbr_de_page = ($count / $_POST [self::MAX_ROWS_QUERY_PARAM]);
            $i = 0;
            $p = 1;
            while ( $i < $nbr_de_page ) {
                ?>
                <option value="<?php echo $_POST[self::MAX_ROWS_QUERY_PARAM]*$i?>" id="<?php echo $_POST[self::MAX_ROWS_QUERY_PARAM]?>b"><?php echo $p ?></option>
<?php
                $i ++;
                $p ++;
            }
        }
        ?>
        </select> Nombre de lignes : <select name="max_rows" id="id_of_select" onChange="submit();">
                <option value="9999" selected="selected"></option>
                <option value="25" id="25">25</option>
                <option value="50" id="50">50</option>
                <option value="100" id="100">100</option>
                <option value="250" id="250">250</option>
                <option value="500" id="500">500</option>
            </select>
        </form>

        <form method="post" action="" accept-charset="UTF-8" style="display: inline;">
            Requête : <input type="text" name="requete" id="requete" size="15"> <input type="submit" value="Rechercher"
                alt="Lancer la recherche!">
        </form>
    </div>

<?php
        // détermine le nombre d'entrées à afficher grâce au résultat du formulaire du select, valeur par défaut dans un else à 25 entrées par page
        if (isset($_POST [self::MAX_ROWS_QUERY_PARAM])) {
            $max_rows = $_POST [self::MAX_ROWS_QUERY_PARAM];
        } else {
            $max_rows = 9999;
        }

        $has_request = isset($_POST [self::REQUEST_QUERY_PARAM]);
        $has_pos = isset($_POST [self::POS_QUERY_PARAM]);
        if ($has_request && ! $has_pos) {
            $requete = $_POST [self::REQUEST_QUERY_PARAM];
            $resultats = $wpdb->get_results("SELECT * FROM $this->concepts_table_name WHERE concept LIKE '%$requete%' ORDER BY concept ASC");
        } else if ($has_request && $has_pos) {
            $requete = $_POST [self::REQUEST_QUERY_PARAM];
            $pos = $_POST [self::POS_QUERY_PARAM];
            $resultats = $wpdb->get_results("SELECT * FROM $this->concepts_table_name WHERE concept LIKE '%$requete%' ORDER BY concept ASC LIMIT $max_rows OFFSET $pos");
        } else if (! $has_request && $has_pos) {
            $pos = $_POST [self::POS_QUERY_PARAM];
            $resultats = $wpdb->get_results("SELECT * FROM $this->concepts_table_name ORDER BY concept ASC LIMIT $max_rows OFFSET $pos");
        } else {
            $resultats = $wpdb->get_results("SELECT * FROM $this->concepts_table_name ORDER BY concept ASC");
        }

        $can_update = ! $has_request && ! $has_pos;
        ?>
        <table class="concepts_table">
        <thead class="titre">
            <tr class="titre_aspect">
                <th>Concept</th>
                <th>Définition</th>
                <th>ALPic Number</th>
                <th>ALPic Title</th>
                <th>ALPic Number 2</th>
                <th>ALPic Title 2</th>
                <th>ALW Number</th>
                <th>ALW Title</th>
                <th>ALW Number 2</th>
                <th>ALW Title 2</th>
                <th>ALF Number</th>
                <th>ALF Title</th>
                <th>ALF Number 2</th>
                <th>ALF Title 2</th>
                <th>Commentaires</th>
                <th>Hidden</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="overflow">
<?php
        $i = 0;
        foreach ( $resultats as $post ) {
            $i = $i + 1;
            ?>
                <tr>
                <td><?php echo $post->concept; ?></td>
                <?php
                if (isset($post->definition)) {
                    echo '<td><div class="spoiler"><input type="button" class="boutonSpoiler" value="Afficher" onclick="ouvrirFermerSpoiler(this);" style="display:inline;"><div class="contenu_spoiler" style="display: none;">' . $post->definition . '</div></div></td>';
                } else {
                    echo '<td></td>';
                }
                ?>
                <td><?php echo $post->alpic_number; ?></td>
                <td><?php echo $post->alpic_title; ?></td>
                <td><?php echo $post->alpic_number_2; ?></td>
                <td><?php echo $post->alpic_title_2; ?></td>
                <td><?php echo $post->alw_number; ?></td>
                <td><?php echo $post->alw_title; ?></td>
                <td><?php echo $post->alw_number_2; ?></td>
                <td><?php echo $post->alw_title_2; ?></td>
                <td><?php echo $post->alf_number; ?></td>
                <td><?php echo $post->alf_title; ?></td>
                <td><?php echo $post->alf_number_2; ?></td>
                <td><?php echo $post->alf_title_2; ?></td>
                <?php
                if ($post->commentaires !== '') {
                    echo '<td><div class="spoiler"><input type="button" class="boutonSpoiler" value="Afficher" onclick="ouvrirFermerSpoiler(this);" style="display:inline;"><div class="contenu_spoiler" style="display: none;">' . $post->commentaires . '</div></div></td>';
                } else {
                    echo '<td></td>';
                }
                ?>
                <td><?php echo $post->hidden; ?></td>
                <td>
                    <div class="update-form<?php echo !$can_update ? ' hidden' : ''; ?>">
                        <form method="POST" name="<?php echo 'post'.$i; ?>">
                            <select name="colonne<?php echo $i; ?>">
                                <option value="concept">Concept</option>
                                <option value="definition">Définition</option>
                                <option value="alpic_number">Alpic number</option>
                                <option value="alpic_title">Alpic title</option>
                                <option value="alpic_number_2">Alpic number 2</option>
                                <option value="alpic_title_2">Alpic title 2</option>
                                <option value="alw_number">Alw number</option>
                                <option value="alw_title">Alw title</option>
                                <option value="alw_number_2">Alw number 2</option>
                                <option value="alw_title_2">Alw title 2</option>
                                <option value="alf_number">Alf number</option>
                                <option value="alf_title">Alf title</option>
                                <option value="alf_number_2">Alf number 2</option>
                                <option value="alf_title_2">Alf title 2</option>
                                <option value="commentaires">Commentaires</option>
                            </select>
                            <select name="action<?php echo $i; ?>">
                                <option value="update">update</option>
                                <option value="delete">delete</option>
                                <option value="hide">hide</option>
                            </select> <input type="text" name="update_entry<?php echo $i; ?>"
                                id="update_entry<?php echo $i; ?>" size="10"> <input class="button_upload_change"
                                name="upload_change" type="submit" value="Exécuter"
                                <?php echo 'onClick="return submitForm(post'.$i.');"'; ?>>
                        </form>
                    </div>
                </td>
<?php
            if ($can_update && isset($_POST ['action' . $i]) && isset($_POST ['colonne' . $i])) {
                $selected_val = $_POST ['action' . $i];
                $selected_val2 = $_POST ['colonne' . $i];
                $update_entry = wp_unslash($_POST ['update_entry' . $i]);

                if (isset($selected_val)) {
                    if ($selected_val == 'update' && isset($selected_val2)) {
                        $result = $wpdb->update($this->concepts_table_name, array (
                                $selected_val2 => $update_entry
                        ), array (
                                'concept' => $post->concept
                        ));
                        if ($result > 0) {
                            echo "<script>alert('Successfully Updated')</script>";
                        }
                    } elseif ($selected_val == 'hide') {
                        if ($post->hidden == 'NO') {
                            $result = $wpdb->update($this->concepts_table_name, array (
                                    'hidden' => 'YES'
                            ), array (
                                    'concept' => $post->concept
                            ));
                            if ($result > 0) {
                                echo "<script>alert('Successfully Hidden')</script>";
                            }
                        } else {
                            $result = $wpdb->update($this->concepts_table_name, array (
                                    'hidden' => 'NO'
                            ), array (
                                    'concept' => $post->concept
                            ));
                            if ($result > 0) {
                                echo "<script>alert('Successfully Showed')</script>";
                            }
                        }
                    } elseif ($selected_val == 'delete') {
                        $result = $wpdb->delete($this->concepts_table_name, array (
                            'concept' => $post->concept
                        ));
                        if ($result > 0) {
                            echo "<script>alert('Successfully Deleted')</script>";
                        }
                        $wpdb->flush();
                    }
                }
            }
        }
        ?>
                </tr>
        </tbody>
    </table>

    <h2>Ajouter une entrée :</h2>
    <form method="post" action="" accept-charset="UTF-8">

        <table class="ajout_entree">
            <tr>
                <th scope="row">Concept</th>
                <td><input type="text" name="concept" id="concept" size="10"></td>
            </tr>

            <tr>
                <th scope="row">Définition</th>
                <td><input type="text" name="definition" id="definition" size="10"></td>
            </tr>

            <tr>
                <th>Alpic number</th>
                <td><input type="text" name="alpic_number" id="alpic_number" size="10"></td>

                <th>Alpic title</th>
                <td><input type="text" name="alpic_title" id="alpic_title" size="10"></td>
            </tr>

            <tr>
                <th>Alpic number 2</th>
                <td><input type="text" name="alpic_number_2" id="alpic_number_2" size="10"></td>

                <th>Alpic title 2</th>
                <td><input type="text" name="alpic_title_2" id="alpic_title_2" size="10"></td>
            </tr>

            <tr>
                <th scope="row">Alw number</th>
                <td><input type="text" name="alw_number" id="alw_number" size="10"></td>

                <th scope="row">Alw title</th>
                <td><input type="text" name="alw_title" id="alw_title" size="10"></td>
            </tr>

            <tr>
                <th scope="row">Alw number 2</th>
                <td><input type="text" name="alw_number_2" id="alw_number_2" size="10"></td>

                <th scope="row">Alw title 2</th>
                <td><input type="text" name="alw_title_2" id="alw_title_2" size="10"></td>
            </tr>

            <tr>
                <th scope="row">Alf number</th>
                <td><input type="text" name="alf_number" id="alf_number" size="10"></td>

                <th scope="row">Alf title</th>
                <td><input type="text" name="alf_title" id="alf_title" size="10">

                <td>

            </tr>

            <tr>
                <th scope="row">Alf number 2</th>
                <td><input type="text" name="alf_number_2" id="alf_number_2" size="10"></td>

                <th scope="row">Alf title 2</th>
                <td><input type="text" name="alf_title_2" id="alf_title_2" size="10"></td>
            </tr>

            <tr>
                <th scope="row">Commentaires</th>
                <td><input type="text" name="commentaires" id="commentaires" size="10"></td>
            </tr>

        </table>

        <input type="submit" value="Ajouter une entrée" alt="Insérer!">

    </form>

<?php
        global $wpdb;
        $concept = $_POST ['concept'];

        if (isset($_POST ['concept']) && $_POST ['concept'] !== '') {
            // créer une variable qui compte le nombre d'entrée ayant le même nom de concept que celle émise par le formulaire précédent, si la ligne est déjà existante, un message sera affiché en conséquence
            $count = $wpdb->get_var("SELECT COUNT(concept) FROM $this->concepts_table_name WHERE concept = '$concept'");

            if ($count) {
                ?><script>
     alert("Le concept est déjà pris !");
	 </script>
	 <?php
            } else {
                global $wpdb;

                $wpdb->insert("$this->concepts_table_name", array (
                        'concept' => $_POST ['concept'],
                        'definition' => $_POST ['definition'],
                        'alpic_number' => $_POST ['alpic_number'],
                        'alpic_title' => $_POST ['alpic_title'],
                        'alpic_number_2' => $_POST ['alpic_number_2'],
                        'alpic_title_2' => $_POST ['alpic_title_2'],
                        'alw_number' => $_POST ['alw_number'],
                        'alw_title' => $_POST ['alw_title'],
                        'alw_number_2' => $_POST ['alw_number_2'],
                        'alw_title_2' => $_POST ['alw_title_2'],
                        'alf_number' => $_POST ['alf_number'],
                        'alf_title' => $_POST ['alf_title'],
                        'alf_number_2' => $_POST ['alf_number_2'],
                        'alf_title_2' => $_POST ['alf_title_2'],
                        'commentaires' => $_POST ['commentaires'],
                        'hidden' => 'YES'
                ));
                ?>
	<script>
		alert("L'entrée a été ajoutée !");
	 </script>
<?php
            }
        }
        ?>


                </div>

<h2>Lien des cartes</h2>

<form method="POST" action="options.php">
    <?php settings_fields( self::SETTINGS_GROUP ); ?>
    <?php do_settings_sections( self::SETTINGS_GROUP ); ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row">Maps base URL</th>
            <td><input type="text" name="<?php echo self::MAPS_BASE_URL_SETTING ?>"
                value="<?php echo esc_attr( $this->maps_base_url() ); ?>" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>

<h2>FAQ</h2>

<ul>
    <li><strong>Q : Comment insérer une ligne dans la base de données ?</strong></li>
    <li>Pour insérer une ligne dans la base de données, il faut remplir les champs dans la partie "Ajouter une entrée".
        Le seul obligatoire pour ajouter une entrée est le "Concept", le reste étant facultatif. Tous ces champs peuvent
        être modifiés par la suite via l'action "update". Pour que cette nouvelle ligne s'affiche sur la page du plugin,
        il faut recharger cette dernière. Il est important de noter qu'une entrée ainsi ajoutée ne s'affichera pas tout
        de suite sur le site "atlas-picard" puisqu'elle est configurée pour être masquée. Il faudra effectuer l'action
        "hide" pour l'afficher.</li>
    <li><strong>Q : Comment modifier un champ de la base de données ?</strong>

    <li>

    <li>Pour modifier un champ de la base de données, il faut d'abord sélectionner à l'aide de la première liste
        déroulante la colonne que l'on souhaite modifier ("Concept", "Alpic number", etc.) puis dans la seconde liste
        déroulante l'action "update". Ensuite il faut écrire le nouveau texte dans le champ de saisie prévu à cet effet
        et enfin cliquer sur le bouton "Exécuter" pour effectuer la modification. Exemple, si je veux modifier le numéro
        de l'ALPic Number de l'entrée "abeille", je me place sur la ligne de l'entrée "abeille", puis je choisis dans la
        première liste déroulante la valeur "Alpic number", dans la seconde l'action "update" et je rentre le nouveau
        numéro dans le champ de saisie.</li>
    <li><strong>Q : Comment supprimer une ligne de la base de données ?</strong></li>
    <li>Pour supprimer une ligne dans son entièreté, il suffit de sélectionner l'action "delete" dans la seconde liste
        déroulante et de cliquer sur le bouton "Exécuter".</li>
    <li><strong>Q : Comment masquer une ligne sur le site "atlas-picard" ?</strong></li>
    <li>Pour masquer une ligne sur le site "atlas-picard", il faut sélectionner l'action "hide" dans la seconde liste
        déroulante et cliquer sur le bouton "Exécuter". Pour la réafficher, il suffit d'effectuer la même action et la
        ligne réapparaîtra sur le site. Pour savoir si une ligne est affichée, il faut regarder le champ de la colonne
        "Hidden", "NO" signifiant qu'elle est affichée, "YES" qu'elle est masquée.</li>
    <li><strong>Q : Pourquoi faut-il faire attention avant d'effectuer une modification dans la base de données ?</strong></li>
    <li>Le moindre changement est immédiat et irréversible. C'est pourquoi il est important de s'assurer de ce que l'on
        a sélectionné. S'il est toujours possible de faire une copie de la base de données pour avoir un back-up en cas
        de catastrophe majeure, toutes modifications apportées depuis ce back-up ne seront pas conservées.</li>
    <li><strong>Q : Peut-on rechercher une entrée en particulier ?</strong></li>
    <li>On peut effectuer une recherche via le champ de "requête" au dessus du tableau affichant la base de données et
        qui cherche la valeur du champ de saisie dans les différents concepts.</li>
</ul>

<?php
    }

    const SETTINGS_GROUP = 'appi-mini-settings-group';

    private function maps_base_url() {
        return get_option(self::MAPS_BASE_URL_SETTING);
    }

    const MAPS_BASE_URL_SETTING = 'mini_appi_map_base_url';

    public function register_settings() {
        register_setting(self::SETTINGS_GROUP, self::MAPS_BASE_URL_SETTING);
    }

    public function render_shortcode() {
        ?>

<script type="text/javascript">
function ouvrirFermerSpoiler(bouton) {
    var divContenu = bouton.nextSibling;
    if(divContenu.nodeType == 3) {
        divContenu = divContenu.nextSibling;
    }
    if(divContenu.style.display == 'block') {
        divContenu.style.display = 'none';
    } else {
        divContenu.style.display = 'block';
    }
}
</script>

<?php
        if(isset($_POST [self::REQUEST_QUERY_PARAM])) {
            $requete = $_POST [self::REQUEST_QUERY_PARAM];
            $query = "
                SELECT * FROM $this->concepts_table_name
                WHERE concept LIKE '%$requete%'
                ORDER BY concept ASC
            ";
        } else {
            $query = "SELECT * FROM $this->concepts_table_name ORDER BY concept ASC";
        }

        global $wpdb;
        $maps = $wpdb->get_results($query);

        $lines = array (
                "<table>"
        );
        $lines = array_merge($lines, $this->header_row(array (
                "Concept",
                "Définition",
                "ALPic",
                "ALW",
                "ALF",
                "Commentaires"
        )));
        foreach ( $maps as $map ) {
            if (isset($map->definition)) {
                $definition = '<div class="spoiler"><input type="button" class="boutonSpoiler" value="Afficher" onclick="ouvrirFermerSpoiler(this);" style="display:inline;"><div class="contenu_spoiler" style="display: none;">' . $map->definition . '</div></div>';
            } else {
                $definition = '';
            }

            if ($map->commentaires !== '') {
                $commentaires = '<div class="spoiler"><input type="button" class="boutonSpoiler" value="Afficher" onclick="ouvrirFermerSpoiler(this);" style="display:inline;"><div class="contenu_spoiler" style="display: none;">' . $map->commentaires . '</div></div>';
            } else {
                $commentaires = '';
            }

            $alpic_link = $this->map_link('ALPic', $map);
            if(isset($map->alpic_number_2) && $map->alpic_number_2 !== '') {
                $alpic_link = $alpic_link . '<br>' . $this->map_link('ALPic2', $map);
            }

            $alw_link = $this->map_link('ALW', $map);
            if(isset($map->alw_number_2) && $map->alw_number_2 !== '') {
                $alw_link = $alw_link . '<br>' . $this->map_link('ALW2', $map);
            }

            $alf_link = $this->map_link('ALF', $map);
            if(isset($map->alf_number_2) && $map->alf_number_2 !== '') {
                $alf_link = $alf_link . '<br>' . $this->map_link('ALF2', $map);
            }

            if ($map->hidden === 'NO') {
                $lines = array_merge($lines, $this->row(array (
                        $map->concept,
                        $definition,
                        $alpic_link,
                        $alw_link,
                        $alf_link,
                        $commentaires
                )));
            }
        }
        $lines [] = '</table>';

        return join('', $lines);
    }

    private function header_row($columns) {
        $lines = array (
                "<tr>"
        );
        foreach ( $columns as $column ) {
            $lines [] = '<th>';
            $lines [] = $column;
            $lines [] = '</th>';
        }
        $lines [] = '</tr>';
        return $lines;
    }

    private function row($columns) {
        $lines = array (
                "<tr>"
        );
        foreach ( $columns as $column ) {
            $lines [] = '<td style=" min-width: 5%;">';
            $lines [] = $column;
            $lines [] = '</td>';
        }
        $lines [] = '</tr>';
        return $lines;
    }

    private function map_link($source, $map) {
        $link = null;
        if ($source == "ALPic") {
            $link = $this->download_link($this->alpic_map_url($map->alpic_number), $this->link_text($map->alpic_number, $map->alpic_title));
        } else if ($source == "ALPic2") {
            $link = $this->download_link($this->alw_map_url($map->alpic_number_2), $this->link_text($map->alpic_number_2, $map->alpic_title_2));
        } else if ($source == "ALW") {
            $link = $this->download_link($this->alw_map_url($map->alw_number), $this->link_text($map->alw_number, $map->alw_title));
        } else if ($source == "ALW2") {
            $link = $this->download_link($this->alw_map_url($map->alw_number_2), $this->link_text($map->alw_number_2, $map->alw_title_2));
        } else if ($source == "ALF") {
            $link = $this->download_link($this->alf_map_url($map->alf_number), $this->link_text($map->alf_number, $map->alf_title));
        } else if ($source == "ALF2") {
            $link = $this->download_link($this->alf_map_url($map->alf_number_2), $this->link_text($map->alf_number_2, $map->alf_title_2));
        } else {
            $link = "Unsupported source " . $source;
        }
        return $link;
    }

    private function download_link($relative_url, $text) {
        if ($text == null) {
            return "/";
        } else {
            return '<a target="_blank" href="' . $this->maps_base_url() . $relative_url . '">' . $text . '</a>';
        }
    }

    private function alpic_map_url($number) {
        return '/ALPic/ALPic%23' . $number . '.pdf';
    }

    private function link_text($number, $title) {
        if ($number == null) {
            return null;
        } else {
            return $number . ' <em>' . $title . '</em>';
        }
    }

    private function alw_map_url($number) {
        return '/ALW/ALW%23' . $number . '.pdf';
    }

    private function alf_map_url($number) {
        return '/ALF/ALF%23' . $number . '.pdf';
    }
}
?>