<!-- Modale per l'aggiunta di una voce alle entrate -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIncomeModalLabel">Aggiungi alle entrate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addIncomeStatus"></div>
                <form id="addIncomeForm">
					<div class="form-group">
                        <label for="addIncomeDescription">Descrizione</label>
                        <input type="text" name="name" id="addIncomeName" class="form-control" step="0.01" placeholder="Descrizione" required>
                    </div>
                    <div class="form-group">
                        <label for="addIncomeAmount">Totale</label>
                        <input type="number" name="amount" id="addIncomeAmount" class="form-control" step="0.01" placeholder="Totale" required>
                    </div>
                    <div class="form-group">
                        <label for="addIncomeDate">Data</label>
                        <input type="date" name="added_date" id="addIncomeDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#addIncomeForm').submit(function(e) {
			e.preventDefault();
			var $form = $(this); // Definizione di $form
			$form.find('button[type="submit"]').prop('disabled', true); // Disable submit button
			$.ajax({
				url: 'addIncome.php',
				type: 'POST',
				data: $form.serialize(),
				success: function(response) {
					$('#addIncomeStatus').html('<div class="alert alert-success">Importo aggiunto alle entrate con successo!</div>');
					$('#addIncomeForm')[0].reset();
					setTimeout(function() { window.location.reload(); }, 1000);
				},
				error: function() {
					$('#addIncomeStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				},
				complete: function() {
					$form.find('button[type="submit"]').prop('disabled', false); // Re-enable submit button
				}
			});
		});
    });
</script>