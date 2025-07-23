<div class="notes-grid-container b-t p20">
    <div class="row">
        <?php foreach ($notes as $note) {
            echo view("notes/grid/note", array("note" => $note));
        } ?>
    </div>
</div>

<script>
    function noteGridDeleted(result, $target) {
        var noteId = $target.attr("data-id"),
        $noteGrid = $("#note-grid-" + noteId);

        if ($noteGrid && $($noteGrid).length) {
            $($noteGrid).fadeOut(function () {
                $($noteGrid).remove();
            });
        }
    }

    $(document).ready(function () {
        $(".note-grid-edit-button, #add-note-button").on("click", function () {
            window.isNoteGridView = true;
        });
    });
</script>