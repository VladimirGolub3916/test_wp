(function () {
	'use strict';

	if (typeof testTaskLikes === 'undefined') {
		return;
	}

	function updateVote(root, userVote) {
		var likeButton = root.querySelector('[data-vote-type="like"]');
		var dislikeButton = root.querySelector('[data-vote-type="dislike"]');

		if (!likeButton || !dislikeButton) {
			return;
		}

		likeButton.classList.toggle('is-active', userVote === 1);
		dislikeButton.classList.toggle('is-active', userVote === -1);
		likeButton.setAttribute('aria-pressed', userVote === 1 ? 'true' : 'false');
		dislikeButton.setAttribute('aria-pressed', userVote === -1 ? 'true' : 'false');
		root.dataset.userVote = String(userVote);
	}

	function updateCounts(root, likes) {
		var likeCount = root.querySelector('[data-like-count]');

		if (likeCount) {
			likeCount.textContent = String(likes);
		}
	}

	function sendVote(root, button) {
		var postId = root.dataset.postId;
		var voteType = button.dataset.voteType;

		if (!postId || !voteType || button.classList.contains('is-loading')) {
			return;
		}

		button.classList.add('is-loading');
		button.setAttribute('aria-busy', 'true');

		var payload = new URLSearchParams({
			action: 'test_task_vote',
			nonce: testTaskLikes.nonce,
			postId: postId,
			voteType: voteType,
			pageUrl: window.location.href
		});

		fetch(testTaskLikes.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: payload.toString()
		})
			.then(function (response) {
				return response.json().then(function (data) {
					if (!response.ok || !data.success) {
						throw new Error(data && data.data && data.data.message ? data.data.message : testTaskLikes.error);
					}

					return data.data;
				});
			})
			.then(function (data) {
				updateCounts(root, data.likes);
				updateVote(root, data.userVote);
			})
			.catch(function (error) {
				window.alert(error.message || testTaskLikes.error);
			})
			.finally(function () {
				button.classList.remove('is-loading');
				button.removeAttribute('aria-busy');
			});
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.article-card__vote');

		if (!button) {
			return;
		}

		var root = button.closest('[data-likes-root]');

		if (root) {
			event.preventDefault();
			sendVote(root, button);
		}
	});
})();
