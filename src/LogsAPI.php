<?php

namespace DropTF;

class LogsAPI {
	public function getLogsForPlayer(\SteamID $steamId) {
		$content = file_get_contents('http://logs.tf/json_search?player=' . $steamId->ConvertToUInt64());
		$data = json_decode($content, true);
		return $data['logs'];
	}

	public function getLogsForPlayerSince(\SteamID $steamId, int $since) {
		$allLogs = $this->getLogsForPlayer($steamId);
		return array_filter($allLogs, function (array $log) use ($since) {
			return $log['date'] > $since;
		});
	}

	public function getLog(int $id) {
		$cached = apcu_fetch('log_' . $id);
		$content = $cached ?: file_get_contents('http://logs.tf/json/' . $id);
		if (!$cached) {
			apcu_store('log_' . $id, $content);
		}
		return json_decode($content, true);
	}

	public function getDropsFromLog(int $id, \SteamID $steamId) {
		$log = $this->getLog($id);
		return $log['players'][$steamId->RenderSteam3()]['drops'];
	}

	public function getDropsForPlayersSince(\SteamID $steamId, int $since) {
		$logs = $this->getLogsForPlayerSince($steamId, $since);
		return [array_reduce($logs, function ($drops, $log) use ($steamId) {
			return $drops + $this->getDropsFromLog($log['id'], $steamId);
		}, 0), count($logs)];
	}
}
