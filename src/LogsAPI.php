<?php

namespace DropTF;

class LogsAPI {
	public function getLogsForPlayer(string $steamId) {
		$content = file_get_contents('http://logs.tf/json_search?player=' . $steamId);
		$data = json_decode($content, true);
		return $data['logs'];
	}

	public function getLogsForPlayerSince(string $steamId, int $since) {
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

	public function getDropsFromLog(int $id, string $steamId3) {
		$log = $this->getLog($id);
		return $log['players'][$steamId3]['drops'];
	}

	public function getDropsForPlayersSince(string $steamId64, string $steamId3, int $since) {
		$logs = $this->getLogsForPlayerSince($steamId64, $since);
		return array_reduce($logs, function ($drops, $log) use ($steamId3) {
			return $drops + $this->getDropsFromLog($log['id'], $steamId3);
		}, 0);
	}
}
