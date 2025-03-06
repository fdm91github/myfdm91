<!-- Modale per la modifica di una spesa extra -->
<div class="modal fade" id="editExtraExpenseModal" tabindex="-1" aria-labelledby="editExtraExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExtraExpenseModalLabel">Modifica spesa extra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editExtraExpenseStatus"></div>
                <form id="editExtraExpenseForm" method="POST" action="editExtraExpense.php">
                    <input type="hidden" name="id" id="editExtraExpenseId">
                    <div class="form-group">
                        <label for="editExtraExpenseName">Descrizione</label>
                        <input type="text" name="name" id="editExtraExpenseName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editExtraExpenseAmount">Totale</label>
                        <input type="number" name="amount" id="editExtraExpenseAmount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editExtraExpenseDate">Data</label>
                        <input type="date" name="debit_date" id="editExtraExpenseDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#editExtraExpenseModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
			var name = button.data('name');
			var amount = button.data('amount');
			var date = button.data('date');

			$('#editExtraExpenseId').val(id);
			$('#editExtraExpenseName').val(name);
			$('#editExtraExpenseAmount').val(amount);
			$('#editExtraExpenseDate').val(date);
		});

        $('#editExtraExpenseForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'editExtraExpense.php?id=' + $('#editExtraExpenseId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editExtraExpenseStatus').html('<div class="alert alert-success">Spesa aggiornata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editExtraExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
			});
        });
    });
</script>