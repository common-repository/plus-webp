import apiFetch from '@wordpress/api-fetch';

import { Button } from '@wordpress/components';

import ProgressBar from './progress-bar';

import {
	useRef,
	useState,
	useEffect
} from '@wordpress/element';

const Generate = () => {

	const post_ids = JSON.parse( pluswebpgenerate_data.post_ids );

	const [ currentGenerateprogress, updatecurrentGenerateprogress ] = useState( 0 );
	const [ currentSubmitgenerate, updatecurrentSubmitgenerate ] = useState( false );

	const max_count = post_ids.length;

	const firstUpdateGenerate = useRef( true );
	useEffect( () => {
		if ( firstUpdateGenerate.current ) {
			firstUpdateGenerate.current = false;
			return;
		}
		const result_generate = async () => {
			for ( let i = 0;  i < max_count;  i++  ) {
				await apiFetch( {
					path: 'rf/plus-webp-generate_api/token',
					method: 'POST',
					data: {
						post_id: post_ids[ i ],
						count: i + 1,
						max_count: max_count,
						generate: currentSubmitgenerate,
					}
				} ).then( ( response ) => {
					//console.log( response );
					if ( response['generate'] ) {
						//console.log( i + 1 );
						updatecurrentGenerateprogress( i + 1 );
					} else {
						window.location.reload();
					}
				} );
			}
		}
		try {
			result_generate();
		} catch ( err ) {
			console.log(err);
		}
	}, [ currentSubmitgenerate ] );

	const items_description = [];
	const items_generate_progress = [];
	const items_generate_button = [];
	const items_stop_button = [];
	const items_check_button = [];
	const onclick_submitgenerate = () => {
		updatecurrentSubmitgenerate( true );
	};
	const onclick_submitstop = () => {
		updatecurrentSubmitgenerate( false );
	};
	const onclick_submitcheck = () => {
		window.location.reload();
	};

	if ( currentSubmitgenerate ) {
		items_generate_progress.push(
			<ProgressBar
				p_count = { currentGenerateprogress }
				max_count = { max_count }
				text = { pluswebpgenerate_data.generate_end_label }
				updatecurrentProgress = { updatecurrentGenerateprogress }
				updatecurrentSubmit = { updatecurrentSubmitgenerate }
			/>
		);
		if ( 0 < currentGenerateprogress && currentGenerateprogress < max_count ) {
			items_stop_button.push(
				<Button
					className = { 'button button-large' }
					onClick = { onclick_submitstop }
				>
				{ pluswebpgenerate_data.stop_label }
				</Button>
			);
		}
	} else {
		if ( max_count !== currentGenerateprogress ) {
			items_description.push(
				<>
					<p className="description">
						{ pluswebpgenerate_data.generate_description }
					</p>
					<p className="description">
						{ pluswebpgenerate_data.non_generate_description }
					</p>
				</>
			);
			items_generate_button.push(
				<Button
					className = { 'button button-large' }
					onClick = { onclick_submitgenerate }
				>
				{ pluswebpgenerate_data.generate_label }
				</Button>
			);
		} else {
			items_description.push(
				<p className="description">
					{ pluswebpgenerate_data.non_description }
				</p>
			);
		}
	}

	items_check_button.push(
		<Button
			className = { 'button button-large' }
			onClick = { onclick_submitcheck }
		>
		{ pluswebpgenerate_data.check_label }
		</Button>
	);

	return (
		<>
			<h2>{ pluswebpgenerate_data.bulk_label }</h2>
			<b><li>{ pluswebpgenerate_data.generate_description_label }</li></b>
			<b><li>{ pluswebpgenerate_data.description_label }</li></b>
			<b><li>{ pluswebpgenerate_data.wp_cli_label }</li></b>
			<div className="settings">
				<strong>WP-CLI</strong>
				<code>wp pluswebpavif</code>
			</div>
			{ items_description }
			{ items_generate_button } &nbsp;&nbsp;&nbsp;
			{ items_stop_button } &nbsp;&nbsp;&nbsp;
			{ items_check_button }
			{ items_generate_progress }
		</>
	);

};

export default Generate;
