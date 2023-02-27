<form method="post" action="">
	<div class="panel-body" style="font-family: Franklin Gothic Medium;text-transform: uppercase;color: #9f9f9f;">Настройки плагина</div>
	<div class="table-responsive">
	<table class="table table-striped">
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Оповещение ЛС о выдачи предупреждения:</h6>
		  <span class="text-muted text-size-small hidden-xs">Будет ли отправляться ЛС при получении предупреждения?</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<select name="warned_pm">{{ warned_pm }}</select>
        </td>
      </tr>
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Оповещение ЛС о удалении предупреждения:</h6>
		  <span class="text-muted text-size-small hidden-xs">Будет ли отправляться ЛС при удалении предупреждения?</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<select name="warned_pm_del">{{ warned_pm_del }}</select>
        </td>
      </tr>
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Количество предупреждений для бана:</h6>
		  <span class="text-muted text-size-small hidden-xs">Количество предупреждений после которого пользователь будет автоматически забанен</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<input name="warn_num_ban" type="text" size="4" value="{{ warn_num_ban }}" />
        </td>
      </tr>
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Количество предупреждений в списке:</h6>
		  <span class="text-muted text-size-small hidden-xs">Введите количество, которое будет отображаться в админ панели. (по умолчанию 10)</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<input name="num_news" type="text" size="4" value="{{ num_news }}" />
        </td>
      </tr> 
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Вывод текста для забаненного пользователя:</h6>
		  <span class="text-muted text-size-small hidden-xs">Содержание, которое видит пользователь при заходе на сайт.</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<textarea id="acces" name="acces" class="form-control" rows="10">{{ acces }}</textarea>
        </td>
      </tr>
	</table>
	</div>
	<div class="panel-footer" align="center" style="margin-left: -20px;margin-right: -20px;margin-bottom: -20px;">
		<button type="submit" name="submit" class="btn btn-outline-primary">Сохранить</button>
	</div>
</form>