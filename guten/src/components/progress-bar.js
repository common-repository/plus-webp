import { Notice } from '@wordpress/components';

const ProgressBar = ( props ) => {

	let progress = Math.round( ( props.p_count / props.max_count ) * 100 );

	const items_progress = [];
	items_progress.push(
		<>
			<progress value={ progress } max="100">
			</progress>&nbsp;&nbsp;{ props.p_count }&nbsp;/&nbsp;{ props.max_count }&nbsp;&nbsp;|&nbsp;&nbsp;{ progress } %
		</>
	);

	const items = [];
	items.push(
		<>
		{ items_progress }
		</>
	);
	if ( 100 == progress ) {
		items.push(
			<Notice
				status = "success"
				onRemove = { () =>
					{
						window.location.reload();
					}
				}
			>
			{ props.text }
			</Notice>
		);
	}

	return (
		<div>
			{ items }
		</div>
	);

};

export default ProgressBar;
