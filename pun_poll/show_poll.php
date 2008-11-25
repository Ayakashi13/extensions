<?php

/***********************************************************************

	Copyright (C) 2008  PunBB

	PunBB is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published
	by the Free Software Foundation; either version 2 of the License,
	or (at your option) any later version.

	PunBB is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston,
	MA  02111-1307  USA


	���� ��� ��� ��� ������ �������������� ���� �� ����� ���� - ������ ���� �����
	���� ����� ����, �� ������������ ������� ��������� �������� ���� � �����������!!!!!
	
	� ��������� ��������� ����������
***********************************************************************/

if (!defined('FORUM')) exit;

$answer_number = isset($_POST['pun_poll_radio']) ? intval($_POST['pun_poll_radio']) : 0; 
$pids = isset($_GET['pid']) ? $_GET['pid'] : 0;
$revote = isset($_POST['revote_poll']) ? 1 : 0;

$count = 5;

$pun_query_poll = array(
	'SELECT'	=> 'able_see',
	'FROM'		=> 'questions',
	'WHERE'		=> 'id_topic='.$id
);

$poll_result_see = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

$pun_query_poll = array(
	'SELECT'	=> 'able_rev',
	'FROM'		=> 'questions',
	'WHERE'		=> 'id_topic='.$id
);

$poll_result_rev = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
$num_dis_see = $forum_db->fetch_assoc($poll_result_see);
$num_dis_revote = $forum_db->fetch_assoc($poll_result_rev);

if ($revote)
{
	if ($num_dis_revote['able_rev'] == '1')
	{
		$pun_query_poll = array(
			'DELETE'	=> 'voting',
			'WHERE'		=> '(user="'.$forum_user['username'].'" AND id_topic='.$id.')'
		);

		$forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
	}
}

$pun_query_poll = array(
	'SELECT'	=> 'ball, user',
	'FROM'		=> 'voting',
	'WHERE'		=> 'id_topic='.$id
);

$poll_result = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

$pun_query_poll = array(
	'SELECT'	=> 'ball, user',
	'FROM'		=> 'voting',
	'WHERE'		=> '(id_topic='.$id.' AND user="'.$forum_user['username'].'")'
);

$poll_result_test = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

$pun_query_poll = array(
	'SELECT'	=> 'question',
	'FROM'		=> 'questions',
	'WHERE'		=> 'id_topic='.$id
);

$poll_result_ques = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

$pun_query_poll = array(
	'SELECT'	=> 'answer',
	'FROM'		=> 'answers',
	'WHERE'		=> 'id_ques='.$id,
	'ORDER BY'	=> 'id ASC'
);

$poll_result_ans = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

$pun_query_poll = array(
	'SELECT'	=> 'able_end_day',
	'FROM'		=> 'questions',
	'WHERE'		=> 'id_topic='.$id
);

$poll_result_days = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

if ($forum_db->num_rows($poll_result_ques) == 0 || $forum_db->num_rows($poll_result_ans) < 2)
	return false;

if (!$forum_user['is_guest'])
{
	if (isset($_POST['form_sent']))
	{
		if (($answer_number > 0) && ($answer_number < ($forum_db->num_rows($poll_result_ans) + 1)))
		{
			if ($forum_db->num_rows($poll_result_test)) {}
			else
			{
				$pun_query_poll = array(
					'INSERT'	=> 'id_topic, user, ball',
					'INTO'		=> 'voting',
					'VALUES'	=> $id.', "'.$forum_user['username'].'", '.$answer_number
				);

				$forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
			}
		}
	}
}
?>

	<div class="main-head">
		<h2 class="hn"><span><?php echo $lang_pun_poll['Topic poll'] ?></span></h2>
		
	</div>

<?php

$pun_query_poll = array(
	'SELECT'	=> 'ball, user',
	'FROM'		=> 'voting',
	'WHERE'		=> 'id_topic='.$id
);

$poll_result = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

$pun_query_poll = array(
	'SELECT'	=> 'ball, user',
	'FROM'		=> 'voting',
	'WHERE'		=> '(id_topic='.$id.' AND user="'.$forum_user['username'].'")'
);

$poll_result_test = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

if ($id || $pid)
{
	$poll_allow_days = $forum_db->fetch_assoc($poll_result_days);

	if ($poll_allow_days['able_end_day'] == '0')
		$poll_allow_days['able_end_day'] = time() + 100000;

	if ($num_dis_revote['able_rev'] == '0')
		$revote = 0;

	$arr = array();
	$arr_ans = array();

	if ($forum_db->num_rows($poll_result_ques))
	{
		$row = $forum_db->fetch_assoc($poll_result_ques);
		
		if (($row['question'] != '') && ($forum_db->num_rows($poll_result_ans) > 1))
		{
			$iter = 0;
			$max_length = 0;
			
			while ($row2 = $forum_db->fetch_assoc($poll_result_ans))
			{
				$arr_ans[] = $row2['answer'];

				if ($max_length < strlen($row2['answer'])) $max_length = strlen($row2['answer']);
					$arr[] = 0;
			}
			
			echo '
				<div class="main-subhead">
					<h2 class="hn"><span>'.$lang_pun_poll['Poll question'].':  '.$row['question'].'</span></h2>
				</div>'	
			;
			
			$count = count($arr_ans);
			$count_arr = 0;

			while($row = $forum_db->fetch_assoc($poll_result))
			{
				$arr[$row['ball'] - 1]++;
				$count_arr++;
			}
			
			$polls_page['form_action'] = ($pid) ? forum_link($forum_url['post'], array($pid)) : forum_link($forum_url['topic'], array($id));
			
			$polls_page['hidden_fields'] = array(
				'form_sent'		=> '<input type="hidden" name="form_sent" value="1" />',
				'form_user'	=> '<input type="hidden" name="form_user" value="'.((!$forum_user['is_guest']) ? forum_htmlencode($forum_user['username']) : 'Guest').'" />',
				'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($polls_page['form_action']).'" />'
			);

			?>
			<div class="main-content">
			<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $polls_page['form_action'] ?>">
				<div class="hidden">
					<?php echo implode("\n\t\t\t\t", $polls_page['hidden_fields'])."\n" ?>
				</div>
			
			<?php
			
			echo '<div class="mf-box">';
			for($iter = 0; $iter < count($arr_ans); $iter++)
			{
				if (!$forum_user['is_guest'])
				{
					if ($poll_allow_days['able_end_day'] > time())
					{
						$number_radio = $iter + 1;
						if ($revote || $forum_db->num_rows($poll_result_test) == 0)
						{
							?>
							
							<div class="mf-item">
								<span class="fld-input"><input type="radio" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="pun_poll_radio" value="<?php echo $number_radio; ?> onclick=""<?php (($iter == 0) ? ' checked ' : '') ?> /></span>
								<label for="fld<?php echo $forum_page['fld_count'] ?>">
								<?php 
									echo htmlspecialchars($arr_ans[$iter])  ;
									if (($id || $pid) && ($num_dis_see['able_see'] == 1))
									{
										if ($arr[$iter] == 0)
										{
											echo '	-	'.'0';
										}
										else
										{
											echo '	-	'.$arr[$iter];
										}
									}
								?></label>
							</div>
							
							<?php
						}
					}
				}

				if (($id || $pid) && (($forum_db->num_rows($poll_result_test) != 0) || ($forum_user['is_guest'])))
				{
					if ($arr[$iter] == 0)
					{
						echo '<div class="mf-item"><label>'.htmlspecialchars($arr_ans[$iter]).' - '.'0'.'</label></div>';
					}
					else
					{
						echo '<div class="mf-item"><label>'.htmlspecialchars($arr_ans[$iter]).' - '.$arr[$iter].'</label></div>';
					}
				}
			}
			
			if ($id || $pid)
				echo '<ul class="user-ident ct-legend"><li class="usertitle"><font size=3>'.$lang_pun_poll['Count of voices'].' '.$count_arr.'</font></li></ul>';
			
			echo '</div>';
			
		}
		else
			echo $lang_pun_poll['No questions'].'
				<div class="frm-form">';
	}

	if ($id || $pid)
	{
		$pun_query_poll = array(
			'SELECT'	=> 'able_see',
			'FROM'		=> 'questions',
			'WHERE'		=> 'id_topic='.$id
		);

		$poll_result_see = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

		$pun_query_poll = array(
			'SELECT'	=> 'able_rev',
			'FROM'		=> 'questions',
			'WHERE'		=> 'id_topic='.$id
		);

		$poll_result_rev = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);

		$pun_query_poll = array(
			'SELECT'	=> 'ball, user',
			'FROM'		=> 'voting',
			'WHERE'		=> 'id_topic='.$id
		);
		
		$poll_result = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
		
		$pun_query_poll = array(
			'SELECT'	=> 'ball, user',
			'FROM'		=> 'voting',
			'WHERE'		=> '(id_topic='.$id.' AND user="'.$forum_user['username'].'")'
		);
		
		$poll_result_test = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
		
		$pun_query_poll = array(
			'SELECT'	=> 'question',
			'FROM'		=> 'questions',
			'WHERE'		=> 'id_topic='.$id
		);
		
		$poll_result_ques = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
		$row = $forum_db->fetch_assoc($poll_result_ques);
		$pun_query_poll = array(
			'SELECT'	=> 'able_end_day',
			'FROM'		=> 'questions',
			'WHERE'		=> 'id_topic='.$id
		);
		
		$poll_result_days = $forum_db->query_build($pun_query_poll) or error(__FILE__, __LINE__);
		
		$poll_allow_days = $forum_db->fetch_assoc($poll_result_days);
		
		if ($poll_allow_days['able_end_day'] == '0')
			$poll_allow_days['able_end_day'] = time() + 100000;
		
		if (($row['question'] != '') && ($forum_db->num_rows($poll_result_ans) > 1))
		{
			if (!$forum_user['is_guest'] && ($forum_db->num_rows($poll_result_test) == 0) && (time() < $poll_allow_days['able_end_day']))
			{
				?>
					<div class="frm-buttons">
						<div class="submit"><input type="submit" name="form_sent" value="<?php echo $lang_pun_poll['Submit opinion'] ?>"> </div>
					</div>
				<?php
			}
			else
			{
				if ($forum_user['is_guest'])
					echo '<div class="mf-box">'.$lang_pun_poll['Guest vote'].'</div>';
				else if ($forum_db->num_rows($poll_result_test))
				{
					echo '<div class="mf-box">'.$lang_pun_poll['Already vote'].'</div>';
					
					if (($num_dis_revote['able_rev'] == 1) && (time() < $poll_allow_days['able_end_day']))
					{
						?>
							<div class="frm-buttons">
								<div class="submit"><input type="submit" name="revote_poll" value="<?php echo $lang_pun_poll['Revote'] ?>"> </div>
							</div>
						<?php
					}
				}
			}
		}
	}
}
?>
			
		</form>
	</div>
	

