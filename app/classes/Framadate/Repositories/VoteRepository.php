<?php
namespace Framadate\Repositories;

use Framadate\FramaDB;
use Framadate\Utils;

class VoteRepository extends AbstractRepository {

    function __construct(FramaDB $connect) {
        parent::__construct($connect);
    }

    function allUserVotesByPollId($poll_id) {
        $prepared = $this->prepare('SELECT * FROM `' . Utils::table('vote') . '` WHERE poll_id = ? ORDER BY id');
        $prepared->execute(array($poll_id));

        return $prepared->fetchAll();
    }

    function insertDefault($poll_id, $insert_position) {
        $prepared = $this->prepare('UPDATE `' . Utils::table('vote') . '` SET choices = CONCAT(SUBSTRING(choices, 1, ?), "0", SUBSTRING(choices, ?)) WHERE poll_id = ?');

        return $prepared->execute([$insert_position, $insert_position + 1, $poll_id]);
    }

    function insert($poll_id, $name, $choices) {
        $prepared = $this->prepare('INSERT INTO `' . Utils::table('vote') . '` (poll_id, name, choices) VALUES (?,?,?)');
        $prepared->execute([$poll_id, $name, $choices]);

        $newVote = new \stdClass();
        $newVote->poll_id = $poll_id;
        $newVote->id = $this->lastInsertId();
        $newVote->name = $name;
        $newVote->choices = $choices;

        return $newVote;
    }

    function deleteById($poll_id, $vote_id) {
        $prepared = $this->prepare('DELETE FROM `' . Utils::table('vote') . '` WHERE poll_id = ? AND id = ?');

        return $prepared->execute([$poll_id, $vote_id]);
    }

    /**
     * Delete all votes of a given poll.
     *
     * @param $poll_id int The ID of the given poll.
     * @return bool|null true if action succeeded.
     */
    function deleteByPollId($poll_id) {
        $prepared = $this->prepare('DELETE FROM `' . Utils::table('vote') . '` WHERE poll_id = ?');

        return $prepared->execute([$poll_id]);
    }

    /**
     * Delete all votes made on given moment index.
     *
     * @param $poll_id int The ID of the poll
     * @param $index int The index of the vote into the poll
     * @return bool|null true if action succeeded.
     */
    function deleteByIndex($poll_id, $index) {
        $prepared = $this->prepare('UPDATE `' . Utils::table('vote') . '` SET choices = CONCAT(SUBSTR(choices, 1, ?), SUBSTR(choices, ?)) WHERE poll_id = ?');

        return $prepared->execute([$index, $index + 2, $poll_id]);
    }

    function update($poll_id, $vote_id, $name, $choices) {
        $prepared = $this->prepare('UPDATE `' . Utils::table('vote') . '` SET choices = ?, name = ? WHERE poll_id = ? AND id = ?');

        return $prepared->execute([$choices, $name, $poll_id, $vote_id]);
    }

    public function countByPollId($poll_id) {
        $prepared = $this->prepare('SELECT count(1) nb FROM `' . Utils::table('vote') . '` WHERE poll_id = ?');

        $prepared->execute([$poll_id]);
        $result = $prepared->fetch();
        $prepared->closeCursor();

        return $result->nb;
    }

}
