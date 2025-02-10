<!-- Modale per la modifica di una spesa ricorrente -->
<div class="modal fade" id="editRecurringExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editRecurringExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRecurringExpenseModalLabel">Modifica spesa ricorrente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editRecurringExpenseStatus"></div>
                <form id="editRecurringExpenseForm">
                    <input type="hidden" name="id" id="editRecurringExpenseId">
                    <div class="form-group">
                        <label for="editRecurringExpenseName">Descrizione</label>
                        <input type="text" name="name" id="editRecurringExpenseName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editRecurringExpenseAmount">Totale</label>
                        <input type="number" name="amount" id="editRecurringExpenseAmount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editRecurringExpenseStartMonth">Mese di inizio</label>
                            <select name="start_month" id="editRecurringExpenseStartMonth" class="form-control" required>
                                <option value="" disabled selected>Seleziona il mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="editRecurringExpenseStartYear">Anno di inizio</label>
                            <input type="number" name="start_year" id="editRecurringExpenseStartYear" class="form-control" min="1900" max="2100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editRecurringExpenseEndMonth">Mese di fine</label>
                            <select name="end_month" id="editRecurringExpenseEndMonth" class="form-control">
                                <option value="" disabled selected>Seleziona il mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="editRecurringExpenseEndYear">Anno di fine</label>
                            <input type="number" name="end_year" id="editRecurringExpenseEndYear" class="form-control" min="1900" max="2100">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="undetermined" id="editRecurringExpenseUndetermined" class="form-check-input" onclick="toggleRecurringEndDate('edit')">
                        <label class="form-check-label" for="editRecurringExpenseUndetermined">Indeterminato</label>
                    </div>
                    <div class="form-group">
                        <label for="editRecurringExpenseDebitDate">Data di addebito</label>
                        <input type="number" name="debit_date" id="editRecurringExpenseDebitDate" class="form-control" min="1" max="31" required>
                    </div>
                    <div class="form-group">
                        <label for="editRecurringExpenseBillingFrequency">Frequenza di fatturazione (mesi)</label>
                        <input type="number" name="billing_frequency" id="editRecurringExpenseBillingFrequency" class="form-control" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRecurringEndDate(action) {
        var endMonth = document.getElementById(action + 'RecurringExpenseEndMonth');
        var endYear = document.getElementById(action + 'RecurringExpenseEndYear');
        if (document.getElementById(action + 'RecurringExpenseUndetermined').checked) {
            endMonth.disabled = true;
            endYear.disabled = true;
        } else {
            endMonth.disabled = false;
            endYear.disabled = false;
        }
    }

    $(document).ready(function() {
		$('#editRecurringExpenseModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
			$('#editRecurringExpenseId').val(id);
			var name = button.data('name');
			var amount = button.data('amount');
			var start_month = button.data('start-month');
			var start_year = button.data('start-year');
			var end_month = button.data('end-month');
			var end_year = button.data('end-year');
			var undetermined = button.data('undetermined'); 
			var debit_date = button.data('debit-date');
			var billing_frequency = button.data('billing-frequency');

			var modal = $(this);
			modal.find('#editRecurringExpenseId').val(id);
			modal.find('#editRecurringExpenseName').val(name);
			modal.find('#editRecurringExpenseAmount').val(amount);
			modal.find('#editRecurringExpenseStartMonth').val(start_month);
			modal.find('#editRecurringExpenseStartYear').val(start_year);
			modal.find('#editRecurringExpenseEndMonth').val(end_month);
			modal.find('#editRecurringExpenseEndYear').val(end_year);
			modal.find('#editRecurringExpenseUndetermined').prop('checked', undetermined);
			modal.find('#editRecurringExpenseDebitDate').val(debit_date);
			modal.find('#editRecurringExpenseBillingFrequency').val(billing_frequency);
		});

        $('#editRecurringExpenseForm').submit(function(e) {
            e.preventDefault();
            var startMonth = parseInt($('#editRecurringExpenseStartMonth').val());
            var startYear = parseInt($('#editRecurringExpenseStartYear').val());
            var endMonth = parseInt($('#editRecurringExpenseEndMonth').val());
            var endYear = parseInt($('#editRecurringExpenseEndYear').val());
            var billingFrequency = parseInt($('#editRecurringExpenseBillingFrequency').val());
            var undetermined = $('#editRecurringExpenseUndetermined').prop('checked');

			// Controllo che la data di inizio sia antecedente a quella di fine
            if (!undetermined && (endYear < startYear || (endYear === startYear && endMonth < startMonth))) {
                $('#editRecurringExpenseStatus').html('<div class="alert alert-danger">La data di fine non può essere antecedente alla data di inizio.</div>');
                return;
            }

			// Controllo che la frequenza di fatturazione sia congruente con i mesi inseriti
            if (!undetermined) {
                var totalMonths = (endYear - startYear) * 12 + (endMonth - startMonth +1);
                if (totalMonths < 0 || totalMonths % billingFrequency !== 0) {
                    $('#editRecurringExpenseStatus').html('<div class="alert alert-danger">La data di fine non combacia con la frequenza di fatturazione.</div>');
                    return;
                }
            }

            $.ajax({
                url: 'editRecurringExpense.php?id=' + $('#editRecurringExpenseId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editRecurringExpenseStatus').html('<div class="alert alert-success">Spesa modificata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editRecurringExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
