<!-- Modale per l'eliminazione di una spesa stimata -->
<div class="modal fade" id="deleteEstimatedExpenseModal" tabindex="-1" aria-labelledby="deleteEstimatedExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEstimatedExpenseModalLabel">Elimina spesa stimata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteEstimatedExpenseStatus"></div>
                <p>Sei sicuro di voler eliminare questa voce?</p>
                <form id="deleteEstimatedExpenseForm">
                    <input type="hidden" name="id" id="deleteEstimatedExpenseId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#deleteEstimatedExpenseModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');

            $('#deleteEstimatedExpenseId').val(id);
        });

        $('#deleteEstimatedExpenseForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteEstimatedExpense.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteEstimatedExpenseStatus').html('<div class="alert alert-success">Spesa eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteEstimatedExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
