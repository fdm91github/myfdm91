<!-- Modale per l'eliminazione di una spesa extra -->
<div class="modal fade" id="deleteExtraExpenseModal" tabindex="-1" role="dialog" aria-labelledby="deleteExtraExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteExtraExpenseModalLabel">Elimina spesa extra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteExtraExpenseStatus"></div>
                <p>Sei sicuro di voler eliminare questa voce?</p>
                <form id="deleteExtraExpenseForm" method="POST" action="deleteExtraExpense.php">
                    <input type="hidden" name="id" id="deleteExtraExpenseId">
                    <button type="submit" class="btn btn-danger btn-block">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteExtraExpenseModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteExtraExpenseId').val(id);
		});

        $('#deleteExtraExpenseForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteExtraExpense.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteExtraExpenseStatus').html('<div class="alert alert-success">Spesa eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteExtraExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
