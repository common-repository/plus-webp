import './settings.css';

import apiFetch from '@wordpress/api-fetch';

import { Button, RadioControl, RangeControl, ToggleControl } from '@wordpress/components';

import {
	useState,
	useEffect
} from '@wordpress/element';

const Settings = () => {

	const pluswebp_options = JSON.parse( pluswebpsettings_data.settings );

	const [ currentOptions, updatecurrentOptions ] = useState( pluswebp_options );

	useEffect( () => {
		apiFetch( {
			path: 'rf/plus-webp-settings_api/token',
			method: 'POST',
			data: {
				output_mime: currentOptions['output_mime'],
				quality: currentOptions['quality'],
				types: currentOptions['types'],
				replace: currentOptions['replace'],
				addext: currentOptions['addext'],
			}
		} ).then( ( response ) => {
			//console.log( response );
		} );
	}, [ currentOptions ] );

	const items_output_mime = [];
	if( typeof currentOptions !== 'undefined' ) {
		items_output_mime.push(
			<RadioControl
				selected = { currentOptions['output_mime'] }
				options = { [
					{ label: 'WebP', value: 'image/webp' },
					{ label: 'AVIF', value: 'image/avif' },
				] }
				onChange={ ( value ) => 
					{
						currentOptions['output_mime'] = value;
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
	}

	const items_quality = [];
	if( typeof currentOptions !== 'undefined' ) {
		items_quality.push(
			<RangeControl
				max = { 100 }
				min = { 1 }
				value = { currentOptions['quality'] }
				className = "range_width"
				onChange={ ( value ) => 
					{
						currentOptions['quality'] = value;
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
	}

	const items_types = [];
	if( typeof currentOptions !== 'undefined' ) {
		let is_jpeg = false;
		if ( currentOptions['types'].includes('image/jpeg') ) {
			is_jpeg = true;
		}
		items_types.push(
			<ToggleControl
				label = 'image/jpeg'
				checked = { is_jpeg }
				onChange = { ( value ) =>
					{
						if ( value ) {
							currentOptions['types'].push('image/jpeg');
						} else {
							let val = 'image/jpeg';
							let index = currentOptions['types'].indexOf( val );
							currentOptions['types'].splice( index, 1 )
						}
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
		let is_png = false;
		if ( currentOptions['types'].includes('image/png') ) {
			is_png = true;
		}
		items_types.push(
			<ToggleControl
				label = 'image/png'
				checked = { is_png }
				onChange = { ( value ) =>
					{
						if ( value ) {
							currentOptions['types'].push('image/png');
						} else {
							let val = 'image/png';
							let index = currentOptions['types'].indexOf( val );
							currentOptions['types'].splice( index, 1 )
						}
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
		let is_bmp = false;
		if ( currentOptions['types'].includes('image/bmp') ) {
			is_bmp = true;
		}
		items_types.push(
			<ToggleControl
				label = 'image/bmp'
				checked = { is_bmp }
				onChange = { ( value ) =>
					{
						if ( value ) {
							currentOptions['types'].push('image/bmp');
						} else {
							let val = 'image/bmp';
							let index = currentOptions['types'].indexOf( val );
							currentOptions['types'].splice( index, 1 )
						}
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
		let is_gif = false;
		if ( currentOptions['types'].includes('image/gif') ) {
			is_gif = true;
		}
		items_types.push(
			<ToggleControl
				label = 'image/gif'
				checked = { is_gif }
				onChange = { ( value ) =>
					{
						if ( value ) {
							currentOptions['types'].push('image/gif');
						} else {
							let val = 'image/gif';
							let index = currentOptions['types'].indexOf( val );
							currentOptions['types'].splice( index, 1 )
						}
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
	}

	const items_addext = [];
	if( typeof currentOptions !== 'undefined' ) {
		items_addext.push(
			<ToggleControl
				label = { pluswebpsettings_data.apply_label }
				checked = { currentOptions['addext'] }
				onChange = { ( value ) =>
					{
						currentOptions['addext'] = value;
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
	}

	const items_replace = [];
	if( typeof currentOptions !== 'undefined' ) {
		items_replace.push(
			<ToggleControl
				label = { pluswebpsettings_data.apply_label }
				checked = { currentOptions['replace'] }
				onChange = { ( value ) =>
					{
						currentOptions['replace'] = value;
						let data = Object.assign( {}, currentOptions );
						updatecurrentOptions( data );
					}
				}
			/>
		);
	}

	return (
		<>
			<h2>{ pluswebpsettings_data.settings_label }</h2>
			<div className="settings">
				<h3>{ pluswebpsettings_data.output_mime_label }</h3>
				<div className="settings2">
					{ items_output_mime }
					<p className="description">
						{ pluswebpsettings_data.output_mime_description_label }
					</p>
				</div>
			</div>
			<div className="settings">
				<h3>{ pluswebpsettings_data.quality_label }</h3>
				<div className="settings2">
					{ items_quality }
					<p className="description">
						{ pluswebpsettings_data.quality_description_label }
					</p>
				</div>
			</div>
			<div className="settings">
				<h3>{ pluswebpsettings_data.type_label }</h3>
				<div className="settings2">
					{ items_types }
					<p className="description">
						{ pluswebpsettings_data.type_description_label }
					</p>
				</div>
			</div>
			<div className="settings">
				<h3>{ pluswebpsettings_data.append_label }</h3>
				<div className="settings2">
					{ items_addext }
					<p className="description">
						{ pluswebpsettings_data.append_description_label }
					</p>
				</div>
			</div>
			<div className="settings">
				<h3>{ pluswebpsettings_data.replace_label }</h3>
				<div className="settings2">
					{ items_replace }
					<p className="description">
						{ pluswebpsettings_data.replace_description_label }
						{ pluswebpsettings_data.replace_advanced_description_label }
						<Button
							href = { pluswebpsettings_data.replace_advanced_link }
							variant = "secondary"
							target = "_blank"
						>
						{ pluswebpsettings_data.replace_advanced_text }
						</Button>
					</p>
				</div>
			</div>
		</>
	);

};

export default Settings;
