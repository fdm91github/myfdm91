<!-- Modale per l'eliminazione di una spesa ricorrente -->
<div class="modal fade" id="deleteRecurringExpenseModal" tabindex="-1" aria-labelledby="deleteRecurringExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRecurringExpenseModalLabel">Elimina spesa ricorrente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteRecurringExpenseStatus"></div>
                <p>Sei sicuro di voler eliminare questa voce?</p>
                <form id="deleteRecurringExpenseForm">
                    <input type="hidden" name="id" id="deleteRecurringExpenseId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#deleteRecurringExpenseModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');

            $('#deleteRecurringExpenseId').val(id);
        });

        $('#deleteRecurringExpenseForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteRecurringExpense.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteRecurringExpenseStatus').html('<div class="alert alert-success">Spesa eliminata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteRecurringExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
